<?php

namespace Tests\Feature;

use App\Models\DeviceRawLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DeleteDeviceRawLogsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_deletes_device_raw_logs_older_than_the_retention_window(): void
    {
        $oldLogId = DB::table((new DeviceRawLog)->getTable())->insertGetId([
            'method' => 'POST',
            'endpoint' => '/iclock/cdata',
            'created_at' => now()->subDays(8),
        ]);

        $recentLogId = DB::table((new DeviceRawLog)->getTable())->insertGetId([
            'method' => 'POST',
            'endpoint' => '/iclock/cdata',
            'created_at' => now()->subDays(6),
        ]);

        $this->artisan('adms:prune-device-raw-logs', ['--days' => 7, '--chunk' => 1])
            ->assertSuccessful();

        $this->assertDatabaseMissing(DeviceRawLog::class, [
            'id' => $oldLogId,
        ]);

        $this->assertDatabaseHas(DeviceRawLog::class, [
            'id' => $recentLogId,
        ]);
    }

    public function test_dry_run_does_not_delete_matching_logs(): void
    {
        $oldLogId = DB::table((new DeviceRawLog)->getTable())->insertGetId([
            'method' => 'POST',
            'endpoint' => '/iclock/cdata',
            'created_at' => now()->subDays(8),
        ]);

        $this->artisan('adms:prune-device-raw-logs', ['--dry-run' => true])
            ->assertSuccessful();

        $this->assertDatabaseHas(DeviceRawLog::class, [
            'id' => $oldLogId,
        ]);
    }
}
