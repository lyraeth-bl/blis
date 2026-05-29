<?php

namespace App\Filament\Resources\DeviceRawLogs\Pages;

use App\Filament\Resources\DeviceRawLogs\DeviceRawLogResource;
use App\Services\DeviceRawLogPruner;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Number;

class ListDeviceRawLogs extends ListRecords
{
    protected static string $resource = DeviceRawLogResource::class;

    public function getSubheading(): string
    {
        return 'Log mentah dari mesin fingerprint untuk memantau request ADMS, payload, dan jumlah data yang diproses.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('prune_old_logs')
                ->label('Hapus Log Lama')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Hapus log ADMS lama?')
                ->modalDescription(fn (): string => $this->pruneModalDescription())
                ->modalSubmitActionLabel('Ya, hapus')
                ->action(function (): void {
                    $pruner = app(DeviceRawLogPruner::class);
                    $count = $pruner->count();
                    $deleted = $pruner->delete();

                    Notification::make()
                        ->title('Log ADMS lama dihapus')
                        ->body(Number::format($deleted).' dari '.Number::format($count).' log kandidat berhasil dihapus.')
                        ->success()
                        ->send();
                }),
        ];
    }

    private function pruneModalDescription(): string
    {
        $pruner = app(DeviceRawLogPruner::class);
        $count = $pruner->count();
        $cutoff = $pruner->cutoff()->toDateTimeString();

        return Number::format($count).' log ADMS yang dibuat sebelum '.$cutoff.' akan dihapus. Ini setara dry-run sebelum delete.';
    }
}
