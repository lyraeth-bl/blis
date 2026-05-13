<?php

use App\Jobs\SyncAttendanceJob;
use App\Models\FingerprintDevice;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    FingerprintDevice::all()->each(function ($device) {
        SyncAttendanceJob::dispatch($device->id);
    });
})->everyFiveMinutes()->name('sync-attendance');
