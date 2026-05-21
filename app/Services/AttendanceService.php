<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceFetchLog;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\SpoPostLog;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AttendanceService
{
    public function syncFromDevice(FingerprintDevice $device): array
    {
        $results = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];
        $syncId = (string) Str::uuid();
        $startedAt = microtime(true);
        $fetchLog = AttendanceFetchLog::create([
            'sync_id' => $syncId,
            'fingerprint_device_id' => $device->id,
            'user_id' => auth()->id(),
            'device_name' => $device->name,
            'device_type' => $device->type,
            'device_ip_address' => $device->ip_address,
            'device_port' => $device->port,
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $logs = $device->getClient()->fetchAttLog();
        } catch (\Throwable $e) {
            $fetchLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'finished_at' => now(),
            ]);

            throw $e;
        }

        $fetchLog->update([
            'fetched' => count($logs),
            'first_log_at' => $this->firstLogDateTime($logs),
            'last_log_at' => $this->lastLogDateTime($logs),
            'raw_rows_sample' => array_slice($logs, 0, 5),
        ]);

        foreach ($logs as $log) {
            try {
                $pin = (string) ($log['PIN'] ?? '');
                $rawDateTime = (string) ($log['DateTime'] ?? '');

                if ($pin === '' || $rawDateTime === '') {
                    $results['failed']++;

                    continue;
                }

                $datetime = Carbon::parse($rawDateTime);
                $date = $datetime->toDateString();
                $time = $datetime->toTimeString();

                $attendable = $this->resolveAttendable($device->type, $pin);

                if (! $attendable) {
                    $results['skipped']++;

                    continue;
                }

                $action = $this->processLog($device, $attendable, $datetime, $date, $time, $fetchLog);
                $results[$action]++;
            } catch (\Throwable $e) {
                $results['failed']++;
            }
        }

        $fetchLog->update([
            'status' => 'success',
            ...$results,
            'elapsed_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'finished_at' => now(),
        ]);

        return $results;
    }

    /**
     * @param  array<int, array{DateTime?: string}>  $logs
     */
    protected function firstLogDateTime(array $logs): ?string
    {
        foreach ($logs as $log) {
            if (! blank($log['DateTime'] ?? null)) {
                return (string) $log['DateTime'];
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{DateTime?: string}>  $logs
     */
    protected function lastLogDateTime(array $logs): ?string
    {
        for ($index = count($logs) - 1; $index >= 0; $index--) {
            if (! blank($logs[$index]['DateTime'] ?? null)) {
                return (string) $logs[$index]['DateTime'];
            }
        }

        return null;
    }

    protected function resolveAttendable(string $type, string $pin): ?Model
    {
        return match ($type) {
            'student' => Student::where('nis', $pin)->first(),
            'employee' => Employee::where('nip', $pin)->first(),
            default => null,
        };
    }

    /**
     * @param  array{status1?: mixed, status2?: mixed, status3?: mixed, status4?: mixed, status5?: mixed, raw_payload?: string}  $metadata
     */
    public function syncPushedLog(
        FingerprintDevice $device,
        string $pin,
        Carbon $datetime,
        array $metadata = [],
    ): string {
        $attendable = $this->resolveAttendable($device->type, $pin);

        if (! $attendable) {
            return 'skipped';
        }

        if (! $device->supportsUnit($attendable->unit_id)) {
            return 'skipped';
        }

        $action = $this->processLog(
            device: $device,
            attendable: $attendable,
            datetime: $datetime,
            date: $datetime->toDateString(),
            time: $datetime->toTimeString(),
        );

        Attendance::query()
            ->where('attendable_type', $attendable::class)
            ->where('attendable_id', $attendable->id)
            ->where('date', $datetime->toDateString())
            ->update([
                'fingerprint_device_id' => $device->id,
                'adms_pin' => $pin,
                'adms_punch_time' => $datetime,
                'adms_status1' => $metadata['status1'] ?? null,
                'adms_status2' => $metadata['status2'] ?? null,
                'adms_status3' => $metadata['status3'] ?? null,
                'adms_status4' => $metadata['status4'] ?? null,
                'adms_status5' => $metadata['status5'] ?? null,
                'adms_raw_payload' => $metadata['raw_payload'] ?? null,
            ]);

        return $action;
    }

    protected function processLog(
        FingerprintDevice $device,
        Model $attendable,
        Carbon $datetime,
        string $date,
        string $time,
        ?AttendanceFetchLog $fetchLog = null,
    ): string {
        $checkInStart = Carbon::parse($device->check_in_start);
        $checkInEnd = Carbon::parse($device->check_in_end);
        $checkOutStart = Carbon::parse($device->check_out_start);
        $fingerTime = Carbon::parse($time);

        $existing = Attendance::where('attendable_type', $attendable::class)
            ->where('attendable_id', $attendable->id)
            ->where('date', $date)
            ->first();

        // Jam checkout
        if ($fingerTime->gte($checkOutStart)) {
            if ($existing) {
                $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkOut', $fetchLog);

                $existing->update(['check_out' => $time]);

                $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkOut', $fetchLog);

                return 'updated';
            }

            // Finger checkout tapi ga ada data pagi -> insert dengan status lupa check-in
            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkOut', $fetchLog);

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_out' => $time,
                'status' => 'present',
                'source' => 'fingerprint',
                'description' => 'Lupa check-in',
            ]);

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkOut', $fetchLog);

            return 'inserted';
        }

        // Jam check-in (hadir)
        if ($fingerTime->between($checkInStart, $checkInEnd)) {
            if ($existing) {
                return 'skipped';
            }

            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkIn', $fetchLog);

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'present',
                'source' => 'fingerprint',
            ]);

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkIn', $fetchLog);

            return 'inserted';
        }

        // Antara check_in_end dan check_out_start -> terlambat
        if ($fingerTime->gt($checkInEnd) && $fingerTime->lt($checkOutStart)) {
            if ($existing) {
                return 'skipped';
            }

            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkIn', $fetchLog);

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'late',
                'source' => 'fingerprint',
            ]);

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkIn', $fetchLog);

            return 'inserted';
        }

        return 'skipped';
    }

    /**
     * @return array{nis: string, checkIn?: string, checkOut?: string}
     */
    protected function makeStudentAttendancePayload(Student $student, Carbon $datetime, string $field): array
    {
        return [
            'nis' => $student->nis,
            $field => $datetime->toIso8601String(),
        ];
    }

    protected function sendStudentAttendanceToSpo(
        FingerprintDevice $device,
        Model $attendable,
        Carbon $datetime,
        string $field,
        ?AttendanceFetchLog $fetchLog = null,
    ): void {
        if (! $attendable instanceof Student) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'attendance',
                field: $field,
                status: 'skipped',
                skippedReason: 'Attendable is not a student.',
            );

            return;
        }

        $url = config('spo.attendance_url');

        if (blank($url)) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'attendance',
                field: $field,
                status: 'failed',
                url: $url,
                errorMessage: 'SPO attendance URL is not configured.',
            );

            throw new \RuntimeException('SPO attendance URL is not configured.');
        }

        $payload = $this->makeStudentAttendancePayload($attendable, $datetime, $field);

        try {
            $response = $this->spoRequest()->post($url, $payload);
        } catch (\Throwable $e) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'attendance',
                field: $field,
                status: 'failed',
                url: $url,
                payload: $payload,
                errorMessage: $e->getMessage(),
            );

            throw $e;
        }

        if ($response->successful()) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'attendance',
                field: $field,
                status: 'success',
                url: $url,
                httpStatus: $response->status(),
                payload: $payload,
                responseBody: $response->body(),
            );

            Log::info('AttendanceService.spo_attendance_sent', [
                'device_id' => $device->id,
                'student_id' => $attendable->id,
                'nis' => $attendable->nis,
                'field' => $field,
            ]);

            return;
        }

        $this->logSpoPost(
            fetchLog: $fetchLog,
            device: $device,
            attendable: $attendable,
            endpointType: 'attendance',
            field: $field,
            status: 'failed',
            url: $url,
            httpStatus: $response->status(),
            payload: $payload,
            responseBody: $response->body(),
            errorMessage: "SPO attendance request failed with status {$response->status()}.",
        );

        Log::warning('AttendanceService.spo_attendance_failed', [
            'device_id' => $device->id,
            'student_id' => $attendable->id,
            'nis' => $attendable->nis,
            'field' => $field,
            'status' => $response->status(),
            'body' => str($response->body())->limit(1000)->toString(),
        ]);

        throw new \RuntimeException("SPO attendance request failed with status {$response->status()}.");
    }

    protected function sendStudentAttendanceNotification(
        FingerprintDevice $device,
        Model $attendable,
        Carbon $datetime,
        string $field,
        ?AttendanceFetchLog $fetchLog = null,
    ): void {
        if (! $attendable instanceof Student) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'notification',
                field: $field,
                status: 'skipped',
                skippedReason: 'Attendable is not a student.',
            );

            return;
        }

        $url = config('spo.notify_url');

        if (blank($url)) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'notification',
                field: $field,
                status: 'skipped',
                skippedReason: 'SPO notify URL is not configured.',
            );

            return;
        }

        $payload = $this->makeStudentNotificationPayload($attendable, $datetime, $field);

        try {
            $response = $this->spoRequest()->post($url, $payload);
        } catch (\Throwable $e) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'notification',
                field: $field,
                status: 'failed',
                url: $url,
                payload: $payload,
                errorMessage: $e->getMessage(),
            );

            Log::error('AttendanceService.spo_notify_error', [
                'device_id' => $device->id,
                'student_id' => $attendable->id,
                'nis' => $attendable->nis,
                'field' => $field,
                'err' => $e->getMessage(),
            ]);

            return;
        }

        if ($response->successful()) {
            $this->logSpoPost(
                fetchLog: $fetchLog,
                device: $device,
                attendable: $attendable,
                endpointType: 'notification',
                field: $field,
                status: 'success',
                url: $url,
                httpStatus: $response->status(),
                payload: $payload,
                responseBody: $response->body(),
            );

            Log::info('AttendanceService.spo_notify_sent', [
                'device_id' => $device->id,
                'student_id' => $attendable->id,
                'nis' => $attendable->nis,
                'field' => $field,
            ]);

            return;
        }

        $this->logSpoPost(
            fetchLog: $fetchLog,
            device: $device,
            attendable: $attendable,
            endpointType: 'notification',
            field: $field,
            status: 'failed',
            url: $url,
            httpStatus: $response->status(),
            payload: $payload,
            responseBody: $response->body(),
            errorMessage: "SPO notify request failed with status {$response->status()}.",
        );

        Log::warning('AttendanceService.spo_notify_failed', [
            'device_id' => $device->id,
            'student_id' => $attendable->id,
            'nis' => $attendable->nis,
            'field' => $field,
            'status' => $response->status(),
            'body' => str($response->body())->limit(1000)->toString(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    protected function logSpoPost(
        ?AttendanceFetchLog $fetchLog,
        FingerprintDevice $device,
        ?Model $attendable,
        string $endpointType,
        ?string $field,
        string $status,
        ?string $url = null,
        ?int $httpStatus = null,
        ?array $payload = null,
        ?string $responseBody = null,
        ?string $errorMessage = null,
        ?string $skippedReason = null,
    ): void {
        SpoPostLog::create([
            'attendance_fetch_log_id' => $fetchLog?->id,
            'fingerprint_device_id' => $device->id,
            'attendable_type' => $attendable?->getMorphClass(),
            'attendable_id' => $attendable?->getKey(),
            'endpoint_type' => $endpointType,
            'field' => $field,
            'status' => $status,
            'url' => $url,
            'http_status' => $httpStatus,
            'payload' => $payload,
            'response_body' => filled($responseBody) ? str($responseBody)->limit(5000)->toString() : null,
            'error_message' => $errorMessage,
            'skipped_reason' => $skippedReason,
            'attempted_at' => now(),
        ]);
    }

    /**
     * @return array{targetNis: array<int, string>, title: string, body: string, data: array{type: string, createdAt: string}}
     */
    protected function makeStudentNotificationPayload(Student $student, Carbon $datetime, string $field): array
    {
        $action = $field === 'checkOut' ? 'check-out' : 'check-in';

        return [
            'targetNis' => [$student->nis],
            'title' => "Kamu berhasil melakukan {$action}",
            'body' => 'Absensi tercatat pada '.$datetime->toDateTimeString(),
            'data' => [
                'type' => 'Attendance',
                'createdAt' => now()->toIso8601String(),
            ],
        ];
    }

    protected function spoRequest(): PendingRequest
    {
        return Http::withToken(config('spo.token', ''))
            ->acceptJson()
            ->asJson()
            ->connectTimeout(config('spo.connect_timeout', 5))
            ->timeout(config('spo.timeout', 10))
            ->retry(config('spo.retry.times', 2), config('spo.retry.sleep', 200), throw: false);
    }
}
