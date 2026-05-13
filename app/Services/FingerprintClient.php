<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class FingerprintClient
{
    // Clear data values
    const CLEAR_ALL = '1';

    const CLEAR_FINGERPRINT = '2';

    const CLEAR_ATTENDANCE_LOG = '3';

    protected string $target;

    protected int $port;

    protected string $commKey;

    protected float $timeout;

    public function __construct(?string $target, ?int $port = 80, string $commKey = '0', float $timeout = 2.0)
    {
        if (empty($target) || ! is_string($target)) {
            throw new \InvalidArgumentException('Invalid IP/URL provided to FingerprintClient: '.var_export($target, true));
        }

        $this->target = trim($target);
        $this->port = $port ?: 80;
        $this->commKey = $commKey;
        $this->timeout = max(0.1, (float) $timeout);
    }

    /**
     * Fetch attendance logs. Supports:
     * - full URL (http/https) -> HTTP POST with XML body, parse response
     * - plain host/ip -> use fsockopen and POST /iWsService (legacy)
     *
     * Returns array of rows: each ['PIN'=>..., 'DateTime'=>..., 'Verified'=>..., 'Status'=>..., 'raw_row'=>...]
     */
    public function fetchAttLog(): array
    {

        if (preg_match('#^https?://#i', $this->target)) {
            return $this->fetchViaHttpUrl($this->target);
        }

        return $this->fetchViaSocket($this->target, $this->port);
    }

    protected function fetchViaHttpUrl(string $url): array
    {
        $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">{$this->commKey}</ArgComKey><Arg><PIN xsi:type=\"xsd:integer\">All</PIN></Arg></GetAttLog>";

        try {
            $client = new Client([
                'timeout' => $this->timeout,
                'verify' => false,
            ]);
            $res = $client->post($url, [
                'headers' => ['Content-Type' => 'text/xml'],
                'body' => $soap_request,
            ]);
            $body = (string) $res->getBody();
        } catch (\Throwable $e) {
            Log::error('FingerprintClient.fetchViaHttpUrl_failed', ['url' => $url, 'err' => $e->getMessage()]);
            throw new \RuntimeException("HTTP fetch failed for {$url}: ".$e->getMessage());
        }

        return $this->parseResponseBuffer($body);
    }

    protected function fetchViaSocket(string $host, int $port): array
    {
        $soap_request = "<GetAttLog><ArgComKey xsi:type=\"xsd:integer\">{$this->commKey}</ArgComKey><Arg><PIN>All</PIN></Arg></GetAttLog>";
        $newLine = "\r\n";
        $buffer = '';

        $connect = @fsockopen($host, $port, $errno, $errstr, 5);
        if (! $connect) {
            $msg = "Cannot connect to {$host}:{$port} — $errstr ($errno)";
            Log::error('FingerprintClient.connect_failed', ['host' => $host, 'port' => $port, 'err' => $msg]);
            throw new \RuntimeException($msg);
        }

        stream_set_timeout($connect, (int) floor($this->timeout), (int) (($this->timeout - floor($this->timeout)) * 1000000));

        fwrite($connect, "POST /iWsService HTTP/1.0{$newLine}");
        fwrite($connect, "Content-Type: text/xml{$newLine}");
        fwrite($connect, 'Content-Length: '.strlen($soap_request).$newLine.$newLine);
        fwrite($connect, $soap_request.$newLine);

        while (! feof($connect)) {
            $part = fgets($connect, 1024);
            if ($part === false) {
                break;
            }
            $buffer .= $part;
            if (stripos($buffer, '</GetAttLogResponse>') !== false) {
                break;
            }
        }

        $info = stream_get_meta_data($connect);
        if ($info['timed_out']) {
            Log::warning('FingerprintClient.socket_timeout', ['host' => $host, 'port' => $port, 'timeout' => $this->timeout]);
            fclose($connect);
            throw new \RuntimeException("Socket read timed out for {$host}:{$port}");
        }

        fclose($connect);

        return $this->parseResponseBuffer($buffer);
    }

    protected function parseResponseBuffer(string $buffer): array
    {

        $inner = $this->extract_between($buffer, '<GetAttLogResponse>', '</GetAttLogResponse>');

        if ($inner === '') {
            $inner = $this->extract_between($buffer, '<GetAttLog>', '</GetAttLog>');
        }

        if ($inner === '') {

            Log::warning('FingerprintClient.empty_response', ['raw' => substr($buffer, 0, 2000)]);

            return [];
        }

        $rows = [];
        preg_match_all('/<Row>(.*?)<\/Row>/is', $inner, $matches);
        if (! empty($matches[1])) {
            foreach ($matches[1] as $rowXml) {
                $pin = $this->extract_between($rowXml, '<PIN>', '</PIN>');
                $dt = $this->extract_between($rowXml, '<DateTime>', '</DateTime>');
                $ver = $this->extract_between($rowXml, '<Verified>', '</Verified>');
                $st = $this->extract_between($rowXml, '<Status>', '</Status>');
                $rows[] = [
                    'PIN' => $pin,
                    'DateTime' => $dt,
                    'Verified' => $ver,
                    'Status' => $st,
                    'raw_row' => trim($rowXml),
                ];
            }
        }

        return $rows;
    }

    protected function extract_between(string $str, string $start, string $end): string
    {
        $s = strpos($str, $start);
        if ($s === false) {
            return '';
        }
        $s += strlen($start);
        $e = strpos($str, $end, $s);
        if ($e === false) {
            return '';
        }

        return substr($str, $s, $e - $s);
    }

    /**
     * Push satu user ke mesin.
     * Internally: DeleteUser dulu, lalu SetUserInfo (sesuai behaviour mesin X100C).
     */
    public function setUserInfo(
        string $pin,
        string $name,
        string $password = '',
        string $group = '0',
        string $privilege = '0',
        string $card = '0',
        string $tz1 = '1',
        string $tz2 = '0',
        string $tz3 = '0',
    ): bool {
        // Step 1: delete dulu biar bersih
        $deleteXml = $this->buildXml(
            '<DeleteUser><ArgComKey>%com_key%</ArgComKey><Arg><PIN>%pin%</PIN></Arg></DeleteUser>',
            ['%pin%' => $pin]
        );
        $this->sendXml($deleteXml);

        // Step 2: set user baru
        $setXml = $this->buildXml(
            '<SetUserInfo><ArgComKey>%com_key%</ArgComKey><Arg>'.
            '<Name>%name%</Name><Password>%password%</Password>'.
            '<Group>%group%</Group><Privilege>%privilege%</Privilege>'.
            '<Card>%card%</Card><PIN2>%pin%</PIN2>'.
            '<TZ1>%tz1%</TZ1><TZ2>%tz2%</TZ2><TZ3>%tz3%</TZ3>'.
            '</Arg></SetUserInfo>',
            [
                '%pin%' => $pin,
                '%name%' => $name,
                '%password%' => $password,
                '%group%' => $group,
                '%privilege%' => $privilege,
                '%card%' => $card,
                '%tz1%' => $tz1,
                '%tz2%' => $tz2,
                '%tz3%' => $tz3,
            ]
        );

        $response = $this->sendXml($setXml);

        return $this->isSuccessResponse($response);
    }

    /**
     * Hapus user dari mesin berdasarkan PIN.
     */
    public function deleteUser(string $pin): bool
    {
        $xml = $this->buildXml(
            '<DeleteUser><ArgComKey>%com_key%</ArgComKey><Arg><PIN>%pin%</PIN></Arg></DeleteUser>',
            ['%pin%' => $pin]
        );

        $response = $this->sendXml($xml);

        return $this->isSuccessResponse($response);
    }

    /**
     * Clear absensi data di mesin.
     * $value: '3' = hapus log absensi, 'all' = hapus semua data
     */
    public function clearData(string $value = '3'): bool
    {
        $xml = $this->buildXml(
            '<ClearData><ArgComKey>%com_key%</ArgComKey><Arg><Value>%value%</Value></Arg></ClearData>',
            ['%value%' => $value]
        );

        $response = $this->sendXml($xml);

        return $this->isSuccessResponse($response);
    }

    public function resetAllData(string $value = '1'): bool
    {
        $xml = $this->buildXml(
            '<ClearData><ArgComKey>%com_key%</ArgComKey><Arg><Value>%value%</Value></Arg></ClearData>',
            ['%value%' => $value]
        );

        $response = $this->sendXml($xml);

        return $this->isSuccessResponse($response);
    }

    private function buildXml(string $template, array $params = []): string
    {
        $params['%com_key%'] = $this->commKey;

        return str_replace(array_keys($params), array_values($params), $template);
    }

    /**
     * Kirim XML ke mesin, return raw response string.
     */
    private function sendXml(string $xml): string
    {
        if (preg_match('#^https?://#i', $this->target)) {
            return $this->postHttp($this->target, $xml);
        }

        return $this->postSocket($this->target, $this->port, $xml);
    }

    private function postHttp(string $url, string $xml): string
    {
        try {
            $client = new Client(['timeout' => $this->timeout, 'verify' => false]);
            $res = $client->post($url, [
                'headers' => ['Content-Type' => 'text/xml'],
                'body' => $xml,
            ]);

            return (string) $res->getBody();
        } catch (\Throwable $e) {
            Log::error('FingerprintClient.postHttp_failed', ['url' => $url, 'err' => $e->getMessage()]);
            throw new \RuntimeException('HTTP request failed: '.$e->getMessage());
        }
    }

    private function postSocket(string $host, int $port, string $xml): string
    {
        $newLine = "\r\n";
        $buffer = '';

        $connect = @fsockopen($host, $port, $errno, $errstr, 5);
        if (! $connect) {
            $msg = "Cannot connect to {$host}:{$port} — $errstr ($errno)";
            Log::error('FingerprintClient.connect_failed', ['host' => $host, 'port' => $port, 'err' => $msg]);
            throw new \RuntimeException($msg);
        }

        stream_set_timeout($connect, (int) floor($this->timeout), (int) (($this->timeout - floor($this->timeout)) * 1000000));

        fwrite($connect, "POST /iWsService HTTP/1.0{$newLine}");
        fwrite($connect, "Content-Type: text/xml{$newLine}");
        fwrite($connect, 'Content-Length: '.strlen($xml).$newLine.$newLine);
        fwrite($connect, $xml.$newLine);

        while (! feof($connect)) {
            $part = fgets($connect, 1024);
            if ($part === false) {
                break;
            }
            $buffer .= $part;
        }

        $info = stream_get_meta_data($connect);
        fclose($connect);

        if ($info['timed_out']) {
            Log::warning('FingerprintClient.socket_timeout', ['host' => $host, 'port' => $port]);
            throw new \RuntimeException("Socket timed out for {$host}:{$port}");
        }

        return $buffer;
    }

    private function isSuccessResponse(string $response): bool
    {
        return str_contains($response, 'Successfully')
            || str_contains($response, '<Result>1</Result>')
            || str_contains($response, 'OK');
    }
}
