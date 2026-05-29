<?php

use App\Http\Controllers\AdmsController;
use App\Http\Controllers\Auth\GoogleAuthenticatedSessionController;
use App\Http\Controllers\QrAttendanceController;
use App\Http\Controllers\QrAttendanceScanController;
use App\Http\Controllers\WifiController;
use App\Http\Middleware\EnsureQrAttendanceAccess;
use App\Models\Unit;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/login', [GoogleAuthenticatedSessionController::class, 'create'])
    ->name('login');

Route::get('/login/google', [GoogleAuthenticatedSessionController::class, 'redirect'])
    ->name('login.google');

Route::get('/login/google/callback', [GoogleAuthenticatedSessionController::class, 'callback'])
    ->name('login.google.callback');

Route::post('/logout', [GoogleAuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::get('/', function (Request $request) {
    $user = $request->user();
    $accessibleUnitIds = $user?->accessibleUnitIds() ?? collect();
    $selectedUnitId = Unit::query()->whereKey($request->integer('unit'))->value('id');

    $websites = Website::query()
        ->with('units')
        ->when($selectedUnitId !== null, function ($query) use ($selectedUnitId): void {
            $query->where(function ($query) use ($selectedUnitId): void {
                $query->doesntHave('units')
                    ->orWhereHas('units', fn ($query) => $query->whereKey($selectedUnitId));
            });
        })
        ->where(function ($query) use ($user, $accessibleUnitIds): void {
            $query->where('is_private', false);

            if ($user === null) {
                return;
            }

            $query->orWhere(function ($query) use ($accessibleUnitIds): void {
                $query->where('is_private', true)
                    ->where(function ($query) use ($accessibleUnitIds): void {
                        $query->doesntHave('units');

                        if ($accessibleUnitIds->isNotEmpty()) {
                            $query->orWhereHas('units', fn ($query) => $query->whereKey($accessibleUnitIds->all()));
                        }
                    });
            });
        })
        ->orderBy('category')
        ->orderBy('name')
        ->get();

    $websitesJson = $websites->map(function ($w) {
        return [
            'name' => $w->name,
            'url' => $w->url,
            'category' => $w->category,
            'host' => parse_url($w->url, PHP_URL_HOST) ?: $w->url,
        ];
    })->values();

    $units = Unit::query()->orderBy('name')->orderBy('campus')->get();

    return view('welcome', compact('websites', 'websitesJson', 'units', 'selectedUnitId'));
})->name('home');

Route::get('/wifi', [WifiController::class, 'index'])->name('wifi.index');

Route::middleware(['auth', EnsureQrAttendanceAccess::class])->group(function (): void {
    Route::get('/qr-absensi', QrAttendanceController::class)
        ->name('qr-attendance.index');

    Route::post('/qr-absensi/scan', QrAttendanceScanController::class)
        ->name('qr-attendance.scan');
});

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
