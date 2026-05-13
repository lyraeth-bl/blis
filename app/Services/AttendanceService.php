<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    public function syncFromDevice(FingerprintDevice $device): array
    {
        $results = ['inserted' => 0, 'updated' => 0, 'skipped' => 0, 'failed' => 0];

        try {
            $logs = $device->getClient()->fetchAttLog();
        } catch (\Throwable $e) {
            Log::error('AttendanceService.fetchAttLog_failed', [
                'device_id' => $device->id,
                'err' => $e->getMessage(),
            ]);
            throw $e;
        }

        foreach ($logs as $log) {
            try {
                $pin = $log['PIN'];
                $datetime = Carbon::parse($log['DateTime']);
                $date = $datetime->toDateString();
                $time = $datetime->toTimeString();

                $attendable = $this->resolveAttendable($device->type, $pin);

                if (! $attendable) {
                    $results['skipped']++;

                    continue;
                }

                $action = $this->processLog($device, $attendable, $datetime, $date, $time);
                $results[$action]++;
            } catch (\Throwable $e) {
                Log::error('AttendanceService.processLog_failed', [
                    'device_id' => $device->id,
                    'log' => $log,
                    'err' => $e->getMessage(),
                ]);
                $results['failed']++;
            }
        }

        return $results;
    }

    protected function resolveAttendable(string $type, string $pin): ?Model
    {
        Log::info('AttendanceService.resolveAttendable', [
            'type' => $type,
            'pin' => $pin,
        ]);

        return match ($type) {
            'student' => Student::where('nis', $pin)->first(),
            'employee' => Employee::where('nip', $pin)->first(),
            default => null,
        };
    }

    protected function processLog(
        FingerprintDevice $device,
        Model $attendable,
        Carbon $datetime,
        string $date,
        string $time,
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
                $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkOut');

                $existing->update(['check_out' => $time]);

                $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkOut');

                return 'updated';
            }

            // Finger checkout tapi ga ada data pagi -> insert dengan status lupa check-in
            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkOut');

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

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkOut');

            return 'inserted';
        }

        // Jam check-in (hadir)
        if ($fingerTime->between($checkInStart, $checkInEnd)) {
            if ($existing) {
                return 'skipped';
            }

            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkIn');

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'present',
                'source' => 'fingerprint',
            ]);

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkIn');

            return 'inserted';
        }

        // Antara check_in_end dan check_out_start -> terlambat
        if ($fingerTime->gt($checkInEnd) && $fingerTime->lt($checkOutStart)) {
            if ($existing) {
                return 'skipped';
            }

            $this->sendStudentAttendanceToSpo($device, $attendable, $datetime, 'checkIn');

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'late',
                'source' => 'fingerprint',
            ]);

            $this->sendStudentAttendanceNotification($device, $attendable, $datetime, 'checkIn');

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
    ): void {
        if (! $attendable instanceof Student) {
            return;
        }

        $url = config('spo.attendance_url');

        if (blank($url)) {
            throw new \RuntimeException('SPO attendance URL is not configured.');
        }

        $payload = $this->makeStudentAttendancePayload($attendable, $datetime, $field);
        $response = $this->spoRequest()->post($url, $payload);

        if ($response->successful()) {
            Log::info('AttendanceService.spo_attendance_sent', [
                'device_id' => $device->id,
                'student_id' => $attendable->id,
                'nis' => $attendable->nis,
                'field' => $field,
            ]);

            return;
        }

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
    ): void {
        if (! $attendable instanceof Student) {
            return;
        }

        $url = config('spo.notify_url');

        if (blank($url)) {
            return;
        }

        $payload = $this->makeStudentNotificationPayload($attendable, $datetime, $field);

        try {
            $response = $this->spoRequest()->post($url, $payload);
        } catch (ConnectionException $e) {
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
            Log::info('AttendanceService.spo_notify_sent', [
                'device_id' => $device->id,
                'student_id' => $attendable->id,
                'nis' => $attendable->nis,
                'field' => $field,
            ]);

            return;
        }

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
