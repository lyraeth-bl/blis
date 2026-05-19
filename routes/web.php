<?php

use App\Http\Controllers\WifiController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WifiController::class, 'index'])->name('wifi.index');
