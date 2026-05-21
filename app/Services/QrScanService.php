<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\FingerprintDevice;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class QrScanService
{
    private const TOKEN_TTL = 60;   // detik, sama dengan BE

    private const TOKEN_PARTS = 3;    // NIS:timestamp:hmac

    /**
     * Proses scan QR — support dynamic (HP) dan static (kartu pelajar).
     *
     * @return array{ student: Student, tipe: string, waktu: string, status: string, qr_mode: string }
     *
     * @throws \RuntimeException
     */
    public function process(string $token): array
    {
        // Auto-detect mode dari format token
        // Dynamic → "NIS:timestamp:hmac" (ada 2 titik dua)
        // Static  → "NIS" saja (tidak ada titik dua)
        $isDynamic = substr_count($token, ':') >= 2;

        $nis = $isDynamic ? $this->verifyDynamicToken($token) : $this->verifyStaticToken($token);
        $qrMode = $isDynamic ? 'dynamic' : 'static';

        // Cari Student by NIS di DB lokal
        $student = Student::where('nis', $nis)
            ->select(['id', 'nis', 'name', 'unit', 'class'])
            ->first();

        if (!$student) {
            throw new \RuntimeException("Siswa dengan NIS {$nis} tidak ditemukan.");
        }

        $now = Carbon::now();
        $tipe = $this->resolveAttendanceType($now);

        $attendance = $this->recordAttendance($student, $now, $tipe, $qrMode);

        $this->postToSpo($nis, $now, $tipe);

        return [
            'student' => $student,
            'tipe' => $tipe,
            'waktu' => $now->toIso8601String(),
            'status' => $attendance->status,
            'qr_mode' => $qrMode,
        ];
    }

    // ─── Token Verification ───────────────────────────────────────────────────

    /**
     * Verifikasi dynamic token (dari HP).
     * Format: "NIS:timestamp:hmac"
     *
     * @throws \RuntimeException
     */
    private function verifyDynamicToken(string $token): string
    {
        $parts = explode(':', $token, self::TOKEN_PARTS);

        if (count($parts) !== self::TOKEN_PARTS) {
            throw new \RuntimeException('Token tidak valid.');
        }

        [$nis, $timestamp, $hmac] = $parts;

        // Verifikasi HMAC — hash_equals() mencegah timing attack
        $secret = config('spo.qr_token_secret');
        $expectedHmac = hash_hmac('sha256', "{$nis}:{$timestamp}", $secret);

        if (!hash_equals($expectedHmac, $hmac)) {
            throw new \RuntimeException('Token tidak valid.');
        }

        // Cek expiry
        $tokenAge = Carbon::now()->unix() - (int) $timestamp;

        if ($tokenAge < 0) {
            throw new \RuntimeException('Token tidak valid.');
        }

        if ($tokenAge > self::TOKEN_TTL) {
            throw new \RuntimeException('Token sudah kedaluwarsa.');
        }

        return $nis;
    }

    /**
     * Verifikasi static token (dari kartu pelajar).
     * Format: "NIS" saja — langsung return setelah sanitasi.
     *
     * @throws \RuntimeException
     */
    private function verifyStaticToken(string $token): string
    {
        $nis = trim($token);

        if (blank($nis)) {
            throw new \RuntimeException('Token tidak valid.');
        }

        return $nis;
    }

    // ─── Attendance Logic ─────────────────────────────────────────────────────

    private function resolveAttendanceType(Carbon $now): string
    {
        $device = FingerprintDevice::where('type', 'student')
            ->select(['check_in_end', 'check_out_start'])
            ->first();

        if (!$device) {
            throw new \RuntimeException('Konfigurasi jam absensi tidak ditemukan.');
        }

        $checkOutStart = Carbon::createFromTimeString($device->check_out_start);

        return $now->gte($checkOutStart) ? 'checkOut' : 'checkIn';
    }

    private function recordAttendance(Student $student, Carbon $now, string $tipe, string $qrMode): Attendance
    {
        $device = FingerprintDevice::where('type', 'student')
            ->select(['check_in_end'])
            ->first();

        if (!$device) {
            throw new \RuntimeException('Konfigurasi jam absensi tidak ditemukan.');
        }

        $existing = Attendance::where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->where('date', $now->toDateString())
            ->first();

        if ($tipe === 'checkIn' && $existing) {
            throw new \RuntimeException("{$student->name} sudah melakukan check-in hari ini.");
        }

        if ($tipe === 'checkOut' && $existing?->check_out) {
            throw new \RuntimeException("{$student->name} sudah melakukan check-out hari ini.");
        }

        $checkInEnd = Carbon::createFromTimeString($device->check_in_end);

        $status = match (true) {
            $tipe === 'checkOut' => 'present',
            $now->lte($checkInEnd) => 'present',
            default => 'late',
        };

        // Keterangan mencatat mode QR untuk audit trail
        $description = $qrMode === 'dynamic' ? 'QR Scan - Dynamic' : 'QR Scan - Static';

        if ($tipe === 'checkOut' && $existing) {
            $existing->update([
                'check_out' => $now->toTimeString(),
                'description' => $description,
            ]);

            return $existing->refresh();
        }

        return Attendance::create([
            'attendable_type' => Student::class,
            'attendable_id' => $student->id,
            'date' => $now->toDateString(),
            'status' => $status,
            'check_in' => $tipe === 'checkIn' ? $now->toTimeString() : null,
            'check_out' => $tipe === 'checkOut' ? $now->toTimeString() : null,
            'source' => 'manual',
            'fingerprint_device_id' => null,
            'description' => $tipe === 'checkOut' ? "{$description} - Lupa check-in" : $description,
        ]);
    }

    private function postToSpo(string $nis, Carbon $now, string $tipe): void
    {
        $payload = match ($tipe) {
            'checkIn' => ['nis' => $nis, 'checkIn' => $now->toIso8601String()],
            'checkOut' => ['nis' => $nis, 'checkOut' => $now->toIso8601String()],
        };

        $response = Http::withToken(config('spo.token'))
            ->timeout(config('spo.timeout', 10))
            ->retry(config('spo.retry.times', 2), config('spo.retry.sleep', 200))
            ->post(config('spo.attendance_url'), $payload);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "SPO responded with {$response->status()}: {$response->body()}"
            );
        }
    }
}
