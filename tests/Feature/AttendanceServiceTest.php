<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\FingerprintDevice;
use App\Models\Student;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use ReflectionMethod;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_check_in_is_posted_to_spo_before_local_insert_and_notification(): void
    {
        Http::preventStrayRequests();

        config([
            'spo.attendance_url' => 'https://spo.test/attendance',
            'spo.notify_url' => 'https://spo.test/notify',
            'spo.token' => 'test-token',
            'spo.retry.times' => 1,
        ]);

        $device = FingerprintDevice::create([
            'name' => 'Front Gate',
            'location' => 'School',
            'ip_address' => '192.168.1.2',
            'type' => 'student',
            'check_in_start' => '05:00',
            'check_in_end' => '07:00',
            'check_out_start' => '15:00',
        ]);

        $student = Student::create([
            'nis' => '12345',
            'name' => 'Student One',
            'unit' => 'SMAKT',
        ]);

        Http::fake([
            'https://spo.test/attendance' => function (Request $request) use ($student) {
                $this->assertSame(0, $this->attendanceCountFor($student));
                $this->assertSame([
                    'nis' => '12345',
                    'checkIn' => '2026-05-13T06:30:00+07:00',
                ], $request->data());

                return Http::response(['ok' => true]);
            },
            'https://spo.test/notify' => function (Request $request) use ($student) {
                $this->assertSame(1, $this->attendanceCountFor($student));
                $this->assertSame(['12345'], $request->data()['targetNis']);
                $this->assertSame('Kamu berhasil melakukan check-in', $request->data()['title']);

                return Http::response(['ok' => true]);
            },
        ]);

        $action = $this->processLog(
            $device,
            $student,
            Carbon::parse('2026-05-13 06:30:00'),
        );

        $this->assertSame('inserted', $action);
        $this->assertDatabaseHas(Attendance::class, [
            'attendable_type' => Student::class,
            'attendable_id' => $student->id,
            'fingerprint_device_id' => $device->id,
            'date' => '2026-05-13',
            'check_in' => '06:30:00',
            'status' => 'present',
            'source' => 'fingerprint',
        ]);

        Http::assertSentCount(2);
    }

    public function test_local_attendance_is_not_saved_when_spo_attendance_post_fails(): void
    {
        Http::preventStrayRequests();

        config([
            'spo.attendance_url' => 'https://spo.test/attendance',
            'spo.notify_url' => 'https://spo.test/notify',
            'spo.token' => 'test-token',
            'spo.retry.times' => 1,
        ]);

        $device = FingerprintDevice::create([
            'name' => 'Front Gate',
            'location' => 'School',
            'ip_address' => '192.168.1.2',
            'type' => 'student',
            'check_in_start' => '05:00',
            'check_in_end' => '07:00',
            'check_out_start' => '15:00',
        ]);

        $student = Student::create([
            'nis' => '12345',
            'name' => 'Student One',
            'unit' => 'SMAKT',
        ]);

        Http::fake([
            'https://spo.test/attendance' => Http::response(['message' => 'failed'], 500),
        ]);

        $this->expectException(\RuntimeException::class);

        try {
            $this->processLog(
                $device,
                $student,
                Carbon::parse('2026-05-13 06:30:00'),
            );
        } finally {
            $this->assertDatabaseMissing(Attendance::class, [
                'attendable_type' => Student::class,
                'attendable_id' => $student->id,
                'date' => '2026-05-13',
            ]);

            Http::assertSentCount(1);
        }
    }

    protected function processLog(FingerprintDevice $device, Student $student, Carbon $datetime): string
    {
        $method = new ReflectionMethod(AttendanceService::class, 'processLog');
        $method->setAccessible(true);

        return $method->invoke(
            new AttendanceService,
            $device,
            $student,
            $datetime,
            $datetime->toDateString(),
            $datetime->toTimeString(),
        );
    }

    protected function attendanceCountFor(Student $student): int
    {
        return Attendance::query()
            ->where('attendable_type', $student::class)
            ->where('attendable_id', $student->id)
            ->count();
    }
}
