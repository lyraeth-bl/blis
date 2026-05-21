<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DeviceRawLog;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_handshake_registers_device_and_returns_adms_options(): void
    {
        $response = $this->get('/iclock/cdata?SN=BOCK200961014&options=all');

        $response
            ->assertOk()
            ->assertSeeText('GET OPTION FROM: BOCK200961014')
            ->assertSeeText('Realtime=1')
            ->assertSeeText('Encrypt=0');

        $this->assertDatabaseHas(FingerprintDevice::class, [
            'serial_number' => 'BOCK200961014',
            'name' => 'ADMS BOCK200961014',
        ]);

        $this->assertDatabaseHas(DeviceRawLog::class, [
            'device_serial_number' => 'BOCK200961014',
            'method' => 'GET',
            'endpoint' => 'iclock/cdata',
        ]);

        $this->assertNotNull(FingerprintDevice::firstWhere('serial_number', 'BOCK200961014')->last_seen_at);
    }

    public function test_attlog_payload_creates_employee_attendance(): void
    {
        $device = FingerprintDevice::create([
            'serial_number' => 'EMPLOYEE-SN',
            'name' => 'Employee Device',
            'location' => 'Office',
            'ip_address' => '192.168.1.10',
            'type' => 'employee',
            'check_in_start' => '05:00',
            'check_in_end' => '07:30',
            'check_out_start' => '15:30',
        ]);

        $employee = Employee::create([
            'nip' => '1001',
            'name' => 'Employee One',
            'email' => 'employee@example.test',
            'position' => 'Staff',
        ]);

        $payload = "1001\t2026-05-21 06:45:00\t0\t1\t\t0\t0\n";

        $response = $this->call(
            'POST',
            '/iclock/cdata?SN=EMPLOYEE-SN&table=ATTLOG&Stamp=9999',
            content: $payload,
        );

        $response
            ->assertOk()
            ->assertSeeText('OK: 1');

        $this->assertDatabaseHas(Attendance::class, [
            'attendable_type' => Employee::class,
            'attendable_id' => $employee->id,
            'fingerprint_device_id' => $device->id,
            'date' => '2026-05-21',
            'check_in' => '06:45:00',
            'status' => 'present',
            'source' => 'fingerprint',
            'adms_pin' => '1001',
            'adms_status1' => '0',
            'adms_status2' => '1',
            'adms_status4' => '0',
            'adms_status5' => '0',
            'adms_raw_payload' => trim($payload),
        ]);

        $this->assertDatabaseHas(DeviceRawLog::class, [
            'fingerprint_device_id' => $device->id,
            'device_serial_number' => 'EMPLOYEE-SN',
            'method' => 'POST',
            'endpoint' => 'iclock/cdata',
            'table_name' => 'ATTLOG',
            'processed_count' => 1,
        ]);
    }

    public function test_operlog_payload_is_logged_without_attendance_parsing(): void
    {
        FingerprintDevice::create([
            'serial_number' => 'OPER-SN',
            'name' => 'Operation Device',
            'location' => 'Office',
            'ip_address' => '192.168.1.11',
            'type' => 'student',
        ]);

        $payload = "OPLOG 6\t0\t2026-05-21 12:55:41\t1\t0\t0\t906\nFP PIN=1\tFID=0\tSize=440\tValid=1\tTMP=abc";

        $response = $this->call(
            'POST',
            '/iclock/cdata?SN=OPER-SN&table=OPERLOG',
            content: $payload,
        );

        $response
            ->assertOk()
            ->assertSeeText('OK: 2');

        $this->assertDatabaseCount(Attendance::class, 0);
        $this->assertDatabaseHas(DeviceRawLog::class, [
            'device_serial_number' => 'OPER-SN',
            'table_name' => 'OPERLOG',
            'processed_count' => 2,
        ]);
    }
}
