<?php

namespace App\Http\Controllers;

use App\Models\DeviceRawLog;
use App\Models\FingerprintDevice;
use App\Models\FingerprintDeviceCommand;
use App\Services\AdmsCommandService;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService,
        protected AdmsCommandService $admsCommandService,
    ) {}

    public function handshake(Request $request): Response
    {
        $serialNumber = $this->serialNumberFrom($request);

        if ($serialNumber === '') {
            return response('SN is required', 400)
                ->header('Content-Type', 'text/plain');
        }

        $device = $this->touchDevice($request, $serialNumber);

        $this->logRequest($request, $device, $serialNumber, null, 0);

        return response($this->handshakePayload($serialNumber))
            ->header('Content-Type', 'text/plain');
    }

    public function receiveRecords(Request $request): Response
    {
        $serialNumber = $this->serialNumberFrom($request);
        $tableName = strtoupper((string) $request->query('table', ''));

        if ($serialNumber === '') {
            return response('SN is required', 400)
                ->header('Content-Type', 'text/plain');
        }

        $device = $this->touchDevice($request, $serialNumber);
        $processedCount = $this->processRows($request, $device, $serialNumber, $tableName);

        $this->logRequest($request, $device, $serialNumber, $tableName, $processedCount);

        return response("OK: {$processedCount}")
            ->header('Content-Type', 'text/plain');
    }

    public function getRequest(Request $request): Response
    {
        $serialNumber = $this->serialNumberFrom($request);
        $device = $serialNumber === '' ? null : $this->touchDevice($request, $serialNumber);

        $command = $device instanceof FingerprintDevice
            ? $this->admsCommandService->nextCommandFor($device)
            : null;

        if ($command instanceof FingerprintDeviceCommand) {
            $command->update([
                'status' => FingerprintDeviceCommand::STATUS_SENT,
                'sent_at' => now(),
            ]);
        }

        $this->logRequest($request, $device, $serialNumber, null, $command instanceof FingerprintDeviceCommand ? 1 : 0);

        return response($command instanceof FingerprintDeviceCommand ? $command->iclock_command : 'OK')
            ->header('Content-Type', 'text/plain');
    }

    public function commandReply(Request $request): Response
    {
        $serialNumber = $this->serialNumberFrom($request);

        if ($serialNumber === '') {
            return response('SN is required', 400)
                ->header('Content-Type', 'text/plain');
        }

        $device = $this->touchDevice($request, $serialNumber);
        $processedCount = $this->processCommandReplies($request, $device);

        $this->logRequest($request, $device, $serialNumber, 'DEVICECMD', $processedCount);

        return response('OK')
            ->header('Content-Type', 'text/plain');
    }

    protected function serialNumberFrom(Request $request): string
    {
        return trim((string) $request->query('SN', ''));
    }

    protected function touchDevice(Request $request, string $serialNumber): FingerprintDevice
    {
        $device = FingerprintDevice::firstOrNew(['serial_number' => $serialNumber]);

        if (! $device->exists) {
            $device->forceFill([
                'name' => "ADMS {$serialNumber}",
                'location' => null,
                'ip_address' => null,
                'type' => 'student',
            ]);
        }

        $device->forceFill([
            'last_seen_at' => now(),
        ])->save();

        return $device;
    }

    protected function handshakePayload(string $serialNumber): string
    {
        return implode("\r\n", [
            "GET OPTION FROM: {$serialNumber}",
            'Stamp=9999',
            'OpStamp='.time(),
            'ErrorDelay=60',
            'Delay=30',
            'ResLogDay=18250',
            'ResLogDelCount=10000',
            'ResLogCount=50000',
            'TransTimes=00:00;14:05',
            'TransInterval=1',
            'TransFlag=1111000000',
            'Realtime=1',
            'Encrypt=0',
        ]);
    }

    protected function processRows(
        Request $request,
        FingerprintDevice $device,
        string $serialNumber,
        string $tableName,
    ): int {
        $rows = preg_split('/\r\n|\r|\n/', $request->getContent()) ?: [];

        if ($tableName === 'OPERLOG') {
            return $this->processOperationRows($rows, $device);
        }

        if ($tableName !== 'ATTLOG') {
            return collect($rows)
                ->filter(fn (string $row): bool => trim($row) !== '')
                ->count();
        }

        $processedCount = 0;

        foreach ($rows as $row) {
            if (trim($row) === '') {
                continue;
            }

            $columns = explode("\t", $row);
            $pin = trim((string) ($columns[0] ?? ''));
            $punchTime = trim((string) ($columns[1] ?? ''));

            if ($pin === '' || $punchTime === '') {
                Log::warning('ADMS ATTLOG row skipped: missing pin or punch time', [
                    'serial_number' => $serialNumber,
                    'row' => $row,
                ]);

                continue;
            }

            try {
                $this->attendanceService->syncPushedLog(
                    device: $device,
                    pin: $pin,
                    datetime: Carbon::parse($punchTime),
                    metadata: [
                        'status1' => $columns[2] ?? null,
                        'status2' => $columns[3] ?? null,
                        'status3' => $columns[4] ?? null,
                        'status4' => $columns[5] ?? null,
                        'status5' => $columns[6] ?? null,
                        'raw_payload' => $row,
                    ],
                );

                $processedCount++;
            } catch (\Throwable $e) {
                Log::error('ADMS ATTLOG row failed', [
                    'serial_number' => $serialNumber,
                    'row' => $row,
                    'err' => $e->getMessage(),
                ]);
            }
        }

        return $processedCount;
    }

    /**
     * @param  array<int, string>  $rows
     */
    protected function processOperationRows(array $rows, FingerprintDevice $device): int
    {
        $processedCount = 0;

        foreach ($rows as $row) {
            $row = trim($row);

            if ($row === '') {
                continue;
            }

            if (str_starts_with($row, 'USER ')) {
                $this->admsCommandService->recordQueryUserPayload(
                    device: $device,
                    rawPayload: $row,
                );
            }

            if (str_starts_with($row, 'FP ')) {
                $this->admsCommandService->recordQueryFingerprintTemplatePayload(
                    device: $device,
                    rawPayload: $row,
                );
            }

            $processedCount++;
        }

        return $processedCount;
    }

    protected function processCommandReplies(Request $request, FingerprintDevice $device): int
    {
        $processedCount = 0;

        foreach ($this->commandReplyPayloads($request->getContent()) as $rawReply) {
            parse_str($rawReply, $reply);
            $command = $this->admsCommandService->recordReply(
                device: $device,
                reply: $reply,
                rawReply: $rawReply,
            );

            if ($command instanceof FingerprintDeviceCommand) {
                $processedCount++;
            }
        }

        return $processedCount;
    }

    /**
     * @return array<int, string>
     */
    protected function commandReplyPayloads(string $content): array
    {
        $rows = preg_split('/\r\n|\r|\n/', $content) ?: [];
        $payloads = [];
        $currentPayload = null;

        foreach ($rows as $row) {
            $row = trim($row);

            if ($row === '') {
                continue;
            }

            if (preg_match('/(?:^|[\t&\r\n ])ID=/', $row) === 1) {
                if ($currentPayload !== null) {
                    $payloads[] = $currentPayload;
                }

                $currentPayload = $row;

                continue;
            }

            if ($currentPayload === null) {
                $payloads[] = $row;

                continue;
            }

            $currentPayload .= "\n".$row;
        }

        if ($currentPayload !== null) {
            $payloads[] = $currentPayload;
        }

        return $payloads;
    }

    protected function logRequest(
        Request $request,
        ?FingerprintDevice $device,
        string $serialNumber,
        ?string $tableName,
        int $processedCount,
    ): void {
        DeviceRawLog::create([
            'fingerprint_device_id' => $device?->id,
            'device_serial_number' => $serialNumber === '' ? null : $serialNumber,
            'method' => $request->method(),
            'endpoint' => $request->path(),
            'query_payload' => $request->query(),
            'body_payload' => $request->getContent(),
            'table_name' => $tableName,
            'processed_count' => $processedCount,
        ]);
    }
}
