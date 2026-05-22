<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\DeviceRawLog;
use App\Models\Employee;
use App\Models\FingerprintDevice;
use App\Models\FingerprintDeviceCommand;
use App\Models\Unit;
use App\Services\AdmsCommandService;
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
        $unit = Unit::query()->first();
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
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '1001',
            'name' => 'Employee One',
            'email' => 'employee@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
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

    public function test_getrequest_returns_pending_update_user_command(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'COMMAND-SN',
            'name' => 'Command Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2001',
            'name' => 'Employee Command',
            'email' => 'employee-command@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueUpdateUser($device, $employee);

        $response = $this->get('/iclock/getrequest?SN=COMMAND-SN');

        $response
            ->assertOk()
            ->assertSeeText("C:{$command->command_id}:DATA UPDATE USERINFO")
            ->assertSeeText('PIN=2001')
            ->assertSeeText('Name=Employee Command');

        $this->assertDatabaseHas(FingerprintDeviceCommand::class, [
            'id' => $command->id,
            'status' => FingerprintDeviceCommand::STATUS_SENT,
        ]);
    }

    public function test_getrequest_returns_pending_update_fingerprint_template_command(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'FINGERPRINT-TEMPLATE-SN',
            'name' => 'Fingerprint Template Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2101',
            'name' => 'Employee Finger',
            'email' => 'employee-finger@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueUpdateFingerprintTemplate(
            device: $device,
            attendable: $employee,
            fingerId: 2,
            template: base64_encode('abc'),
            valid: true,
        );

        $response = $this->get('/iclock/getrequest?SN=FINGERPRINT-TEMPLATE-SN');

        $response
            ->assertOk()
            ->assertSeeText("C:{$command->command_id}:DATA UPDATE FINGERTMP")
            ->assertSeeText('PIN=2101')
            ->assertSeeText('FID=2')
            ->assertSeeText('Size=3')
            ->assertSeeText('Valid=1')
            ->assertSeeText('TMP=YWJj');

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::ACTION_UPDATE_FINGERPRINT_TEMPLATE, $command->action);
        $this->assertSame(3, $command->payload['size']);
    }

    public function test_getrequest_returns_pending_query_fingerprint_template_command(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-FINGERPRINT-TEMPLATE-SN',
            'name' => 'Query Fingerprint Template Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2301',
            'name' => 'Employee Query Finger',
            'email' => 'employee-query-finger@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryFingerprintTemplate(
            device: $device,
            attendable: $employee,
            fingerId: 2,
        );

        $response = $this->get('/iclock/getrequest?SN=QUERY-FINGERPRINT-TEMPLATE-SN');

        $response
            ->assertOk()
            ->assertSeeText("C:{$command->command_id}:DATA QUERY FINGERTMP")
            ->assertSeeText('PIN=2301')
            ->assertSeeText('FingerID=2');

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::ACTION_QUERY_FINGERPRINT_TEMPLATE, $command->action);
        $this->assertSame(2, $command->payload['finger_id']);
    }

    public function test_query_fingerprint_template_reads_fp_data_from_operlog_before_device_ack(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-FP-OPERLOG-SN',
            'name' => 'Query FP Operlog Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2401',
            'name' => 'Employee FP Operlog',
            'email' => 'employee-fp-operlog@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryFingerprintTemplate(
            device: $device,
            attendable: $employee,
            fingerId: 3,
        );

        $this->get('/iclock/getrequest?SN=QUERY-FP-OPERLOG-SN')
            ->assertOk();

        $this->call(
            'POST',
            '/iclock/cdata?SN=QUERY-FP-OPERLOG-SN&table=OPERLOG&OpStamp=9999',
            content: "FP PIN=2401\tFID=3\tSize=3\tValid=1\tTMP=YWJj\n",
        )->assertOk();

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-FP-OPERLOG-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA\n",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('2401', $command->reply_payload['PIN']);
        $this->assertSame('3', $command->reply_payload['FID']);
        $this->assertSame('YWJj', $command->reply_payload['TMP']);
    }

    public function test_query_all_fingerprint_templates_groups_fp_operlog_rows_by_finger_id(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-ALL-FP-OPERLOG-SN',
            'name' => 'Query All FP Operlog Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2501',
            'name' => 'Employee All FP Operlog',
            'email' => 'employee-all-fp-operlog@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryFingerprintTemplate(
            device: $device,
            attendable: $employee,
        );

        $this->call(
            'POST',
            '/iclock/cdata?SN=QUERY-ALL-FP-OPERLOG-SN&table=OPERLOG&OpStamp=9999',
            content: "FP PIN=2501\tFID=1\tSize=3\tValid=1\tTMP=YWJj\nFP PIN=2501\tFID=2\tSize=3\tValid=1\tTMP=ZGVm\n",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('YWJj', $command->reply_payload['templates']['1']['TMP']);
        $this->assertSame('ZGVm', $command->reply_payload['templates']['2']['TMP']);
    }

    public function test_update_fingerprint_template_rejects_invalid_base64_template(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'INVALID-FINGERPRINT-TEMPLATE-SN',
            'name' => 'Invalid Fingerprint Template Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '2201',
            'name' => 'Employee Invalid Finger',
            'email' => 'employee-invalid-finger@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('TMP fingerprint harus berupa base64 yang valid.');

        app(AdmsCommandService::class)->queueUpdateFingerprintTemplate(
            device: $device,
            attendable: $employee,
            fingerId: 1,
            template: 'not base64',
        );
    }

    public function test_getrequest_returns_ok_when_no_command_is_pending(): void
    {
        FingerprintDevice::create([
            'serial_number' => 'NO-COMMAND-SN',
            'name' => 'No Command Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);

        $this->get('/iclock/getrequest?SN=NO-COMMAND-SN')
            ->assertOk()
            ->assertSeeText('OK');
    }

    public function test_devicecmd_reply_marks_command_success_and_updates_pushed_at(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'REPLY-SN',
            'name' => 'Reply Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '3001',
            'name' => 'Employee Reply',
            'email' => 'employee-reply@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueUpdateUser($device, $employee);
        $payload = "ID={$command->command_id}&Return=0&CMD=DATA";

        $response = $this->call(
            'POST',
            '/iclock/devicecmd?SN=REPLY-SN',
            content: $payload,
        );

        $response
            ->assertOk()
            ->assertSeeText('OK');

        $this->assertDatabaseHas(FingerprintDeviceCommand::class, [
            'id' => $command->id,
            'status' => FingerprintDeviceCommand::STATUS_SUCCEEDED,
            'return_code' => '0',
            'raw_reply' => $payload,
        ]);

        $this->assertDatabaseHas('fingerprint_device_users', [
            'fingerprint_device_id' => $device->id,
            'attendable_type' => Employee::class,
            'attendable_id' => $employee->id,
        ]);

        $this->assertNotNull($device->employees()->whereKey($employee->id)->first()?->pivot->pushed_at);
    }

    public function test_devicecmd_reply_marks_delete_command_success_and_detaches_user(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'DELETE-REPLY-SN',
            'name' => 'Delete Reply Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '4001',
            'name' => 'Employee Delete',
            'email' => 'employee-delete@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);
        $device->employees()->attach($employee, ['pushed_at' => now()]);

        $command = app(AdmsCommandService::class)->queueDeleteUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=DELETE-REPLY-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA",
        )->assertOk();

        $this->assertDatabaseHas(FingerprintDeviceCommand::class, [
            'id' => $command->id,
            'status' => FingerprintDeviceCommand::STATUS_SUCCEEDED,
        ]);

        $this->assertDatabaseMissing('fingerprint_device_users', [
            'fingerprint_device_id' => $device->id,
            'attendable_type' => Employee::class,
            'attendable_id' => $employee->id,
        ]);
    }

    public function test_query_user_reply_marks_data_as_synced_when_device_payload_matches(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-SYNC-SN',
            'name' => 'Query Sync Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '5001',
            'name' => 'Employee Sync',
            'email' => 'employee-sync@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-SYNC-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA\tPIN=5001\tName=Employee Sync\tPri=0",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('5001', $command->reply_payload['PIN']);
        $this->assertSame('Employee Sync', $command->reply_payload['Name']);
        $this->assertNull($command->comparison_details);
    }

    public function test_query_user_reply_reads_data_from_multiline_device_payload(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-MULTILINE-SN',
            'name' => 'Query Multiline Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '8001',
            'name' => 'Employee Multiline',
            'email' => 'employee-multiline@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-MULTILINE-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA\nPIN=8001\tName=Employee Multiline\tPri=0",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('8001', $command->reply_payload['PIN']);
        $this->assertSame('Employee Multiline', $command->reply_payload['Name']);
    }

    public function test_query_user_reply_reads_command_metadata_from_tab_separated_payload(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-TAB-SN',
            'name' => 'Query Tab Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '9001',
            'name' => 'Employee Tab',
            'email' => 'employee-tab@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-TAB-SN',
            content: "ID={$command->command_id}\tReturn=0\tCMD=DATA\tPIN=9001\tName=Employee Tab\tPri=0",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('9001', $command->reply_payload['PIN']);
        $this->assertSame('Employee Tab', $command->reply_payload['Name']);
    }

    public function test_query_user_reads_user_data_from_operlog_before_device_ack(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-OPERLOG-SN',
            'name' => 'Query Operlog Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '9101',
            'name' => 'Employee Operlog',
            'email' => 'employee-operlog@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->get('/iclock/getrequest?SN=QUERY-OPERLOG-SN')
            ->assertOk();

        $this->call(
            'POST',
            '/iclock/cdata?SN=QUERY-OPERLOG-SN&table=OPERLOG&OpStamp=9999',
            content: "USER PIN=9101\tName=Employee Operlog\tPri=0\tPasswd=\tCard=0\tGrp=1\tTZ=0000000000000000\tVerify=-1\n",
        )->assertOk();

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-OPERLOG-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA\n",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_SYNCED, $command->comparison_status);
        $this->assertSame('9101', $command->reply_payload['PIN']);
        $this->assertSame('Employee Operlog', $command->reply_payload['Name']);
    }

    public function test_query_user_reply_marks_data_as_different_when_device_payload_differs(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-DIFF-SN',
            'name' => 'Query Diff Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '6001',
            'name' => 'Employee Web',
            'email' => 'employee-diff@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-DIFF-SN',
            content: "ID={$command->command_id}&Return=0&CMD=DATA\tPIN=6001\tName=Employee Device\tPri=0",
        )->assertOk();

        $command->refresh();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_DIFFERENT, $command->comparison_status);
        $this->assertSame([
            'web' => 'Employee Web',
            'device' => 'Employee Device',
        ], $command->comparison_details['Name']);
    }

    public function test_query_user_reply_marks_user_as_missing_when_device_returns_failure(): void
    {
        $unit = Unit::query()->first();
        $device = FingerprintDevice::create([
            'serial_number' => 'QUERY-MISSING-SN',
            'name' => 'Query Missing Device',
            'location' => 'Office',
            'ip_address' => null,
            'type' => 'employee',
        ]);
        $device->units()->attach($unit);

        $employee = Employee::create([
            'nip' => '7001',
            'name' => 'Employee Missing',
            'email' => 'employee-missing@example.test',
            'position' => 'Staff',
            'unit_id' => $unit->id,
        ]);

        $command = app(AdmsCommandService::class)->queueQueryUser($device, $employee);

        $this->call(
            'POST',
            '/iclock/devicecmd?SN=QUERY-MISSING-SN',
            content: "ID={$command->command_id}&Return=-1&CMD=DATA",
        )->assertOk();

        $this->assertSame(FingerprintDeviceCommand::COMPARISON_MISSING, $command->refresh()->comparison_status);
    }
}
