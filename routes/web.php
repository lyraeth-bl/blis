<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\QrAttendanceScanController;
use App\Http\Controllers\WifiController;
use App\Models\Website;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $websites = Website::where('is_private', false)->orderBy('category')->orderBy('name')->get();

    $websitesJson = $websites->map(function ($w) {
        return [
            'name' => $w->name,
            'url' => $w->url,
            'category' => $w->category,
            'host' => parse_url($w->url, PHP_URL_HOST) ?: $w->url,
        ];
    })->values();

    return view('welcome', compact('websites', 'websitesJson'));
});

Route::get('/qr-absensi', QrAttendanceController::class)
    ->name('qr-attendance.index');

Route::post('/qr-absensi/scan', QrAttendanceScanController::class)
    ->name('qr-attendance.scan');

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/iclock/cdata', [AdmsController::class, 'handshake'])
        ->name('adms.handshake');

    Route::post('/iclock/cdata', [AdmsController::class, 'receiveRecords'])
        ->name('adms.records');

    Route::get('/iclock/getrequest', [AdmsController::class, 'getRequest'])
        ->name('adms.get-request');

    Route::post('/iclock/devicecmd', [AdmsController::class, 'commandReply'])
        ->name('adms.command-reply');
});

Route::get('/wifi', [WifiController::class, 'index'])->name('wifi.index');
