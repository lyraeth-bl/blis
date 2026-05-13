<?php

namespace App\Jobs;

use App\Models\FingerprintDevice;
use App\Services\AttendanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncAttendanceJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly int $deviceId
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(AttendanceService $service): void
    {
        $device = FingerprintDevice::find($this->deviceId);

        if (!$device) {
            Log::warning('SyncAttendanceJob: device not found', ['device_id' => $this->deviceId]);
            return;
        }

        try {
            $results = $service->syncFromDevice($device);
            Log::info('SyncAttendanceJob: done', ['device_id' => $this->deviceId, 'results' => $results]);
        } catch (\Throwable $e) {
            Log::error('SyncAttendanceJob: failed', ['device_id' => $this->deviceId, 'err' => $e->getMessage()]);
            throw $e;
        }
    }
}
