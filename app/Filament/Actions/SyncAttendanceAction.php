<?php

namespace App\Filament\Actions;

use App\Services\AttendanceService;
use App\Models\FingerprintDevice;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SyncAttendanceAction
{
    public static function make(): Action
    {
        return Action::make('sync_attendance')
            ->label('Sync Absensi')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Sync Absensi dari Mesin')
            ->modalDescription('Fetch data absensi dari mesin fingerprint dan simpan ke database.')
            ->action(function (FingerprintDevice $record): void {
                try {
                    $service = new AttendanceService();
                    $results = $service->syncFromDevice($record);

                    Notification::make()
                        ->title('Sync berhasil')
                        ->body(
                            "Inserted: {$results['inserted']} | " .
                            "Updated: {$results['updated']} | " .
                            "Skipped: {$results['skipped']} | " .
                            "Failed: {$results['failed']}"
                        )
                        ->success()
                        ->send();
                } catch (\Throwable $e) {
                    Notification::make()
                        ->title('Sync gagal')
                        ->body($e->getMessage())
                        ->danger()
                        ->send();
                }
            });
    }
}