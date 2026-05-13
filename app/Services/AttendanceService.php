<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
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

                if (!$attendable) {
                    $results['skipped']++;
                    continue;
                }

                $action = $this->processLog($device, $attendable, $date, $time);
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
                $existing->update(['check_out' => $time]);
                return 'updated';
            }

            // Finger checkout tapi ga ada data pagi -> insert dengan status lupa check-in
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
            return 'inserted';
        }

        // Jam check-in (hadir)
        if ($fingerTime->between($checkInStart, $checkInEnd)) {
            if ($existing) {
                return 'skipped';
            }

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'present',
                'source' => 'fingerprint',
            ]);
            return 'inserted';
        }

        // Antara check_in_end dan check_out_start -> terlambat
        if ($fingerTime->gt($checkInEnd) && $fingerTime->lt($checkOutStart)) {
            if ($existing) {
                return 'skipped';
            }

            Attendance::create([
                'attendable_type' => $attendable::class,
                'attendable_id' => $attendable->id,
                'fingerprint_device_id' => $device->id,
                'date' => $date,
                'check_in' => $time,
                'status' => 'late',
                'source' => 'fingerprint',
            ]);
            return 'inserted';
        }

        return 'skipped';
    }
}