<?php

use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\QrAttendanceScanController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/qr-absensi', QrAttendanceController::class)
    ->name('qr-attendance.index');

Route::post('/qr-absensi/scan', QrAttendanceScanController::class)
    ->name('qr-attendance.scan');
