<?php

namespace App\Filament\Actions\BulkActions;

use App\Models\FingerprintDevice;
use App\Services\AdmsCommandService;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class DeleteFromDeviceBulkAction
{
    public static function make(string $type): BulkAction
    {
        return BulkAction::make('bulk_delete_from_device')
            ->label('Hapus dari Device')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->form([
                CheckboxList::make('device_ids')
                    ->label('Pilih Device')
                    ->options(fn (): array => FingerprintDevice::query()
                        ->where('type', $type)
                        ->with('units')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (FingerprintDevice $device): array => [
                            $device->id => filled($device->unit_display_names)
                                ? "{$device->name} ({$device->unit_display_names})"
                                : $device->name,
                        ])
                        ->all())
                    ->required(),
            ])
            ->action(function (Collection $records, array $data): void {
                $results = ['success' => [], 'failed' => []];
                foreach ($data['device_ids'] as $deviceId) {
                    $device = FingerprintDevice::find($deviceId);
                    if (! $device) {
                        continue;
                    }

                    foreach ($records as $record) {
                        if (! $device->supportsUnit($record->unit_id)) {
                            $results['failed'][] = $record->name.' (unit berbeda)';

                            continue;
                        }

                        try {
                            app(AdmsCommandService::class)->queueDeleteUser(
                                device: $device,
                                attendable: $record,
                                requestedBy: Auth::user(),
                            );

                            $results['success'][] = $record->name;
                        } catch (\Throwable $e) {
                            $results['failed'][] = $record->name.' ('.$e->getMessage().')';
                        }
                    }
                }

                if (! empty($results['success'])) {
                    Notification::make()
                        ->title(count($results['success']).' command hapus ADMS dibuat')
                        ->success()
                        ->send();
                }

                if (! empty($results['failed'])) {
                    Notification::make()
                        ->title(count($results['failed']).' data gagal: '.implode(', ', $results['failed']))
                        ->danger()
                        ->send();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}
