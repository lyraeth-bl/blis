<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\QrAttendanceScanController;
use App\Http\Controllers\WifiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
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
});

Route::get('/', [WifiController::class, 'index'])->name('wifi.index');
