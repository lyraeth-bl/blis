<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\FingerprintDevice;
use App\Models\Student;
use App\Services\QrScanService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class QrScanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_duplicate_check_in_is_rejected_without_posting_to_spo_again(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://spo.test/attendance' => Http::response(['ok' => true]),
        ]);

        $student = $this->createStudentWithAttendanceDevice();

        Carbon::setTestNow('2026-05-20 06:30:00');

        app(QrScanService::class)->process($student->nis);

        Carbon::setTestNow('2026-05-20 06:35:00');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("{$student->name} sudah melakukan check-in hari ini.");

        try {
            app(QrScanService::class)->process($student->nis);
        } finally {
            $this->assertSame(1, $this->attendanceCountFor($student));
            Http::assertSentCount(1);
        }
    }

    public function test_check_out_updates_existing_attendance_once(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://spo.test/attendance' => Http::response(['ok' => true]),
        ]);

        $student = $this->createStudentWithAttendanceDevice();

        Carbon::setTestNow('2026-05-20 06:30:00');

        app(QrScanService::class)->process($student->nis);

        Carbon::setTestNow('2026-05-20 15:05:00');

        app(QrScanService::class)->process($student->nis);

        $this->assertSame(1, $this->attendanceCountFor($student));
        $this->assertDatabaseHas(Attendance::class, [
            'attendable_type' => Student::class,
            'attendable_id' => $student->id,
            'date' => '2026-05-20',
            'check_in' => '06:30:00',
            'check_out' => '15:05:00',
        ]);

        Carbon::setTestNow('2026-05-20 15:10:00');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("{$student->name} sudah melakukan check-out hari ini.");

        try {
            app(QrScanService::class)->process($student->nis);
        } finally {
            $this->assertSame(1, $this->attendanceCountFor($student));
            Http::assertSentCount(2);
        }
    }

    protected function createStudentWithAttendanceDevice(): Student
    {
        config([
            'spo.attendance_url' => 'https://spo.test/attendance',
            'spo.token' => 'test-token',
            'spo.retry.times' => 1,
            'spo.retry.sleep' => 1,
        ]);

        FingerprintDevice::create([
            'name' => 'Front Gate',
            'location' => 'School',
            'ip_address' => '192.168.1.2',
            'type' => 'student',
            'check_in_start' => '05:00',
            'check_in_end' => '07:00',
            'check_out_start' => '15:00',
        ]);

        return Student::create([
            'nis' => '12345',
            'name' => 'Student One',
            'unit' => 'SMAKT',
        ]);
    }

    protected function attendanceCountFor(Student $student): int
    {
        return Attendance::query()
            ->where('attendable_type', Student::class)
            ->where('attendable_id', $student->id)
            ->count();
    }
}
