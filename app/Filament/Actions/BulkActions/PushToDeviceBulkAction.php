<?php

namespace App\Filament\Actions\BulkActions;

use App\Models\FingerprintDevice;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

class PushToDeviceBulkAction
{
    public static function make(string $type): BulkAction
    {
        return BulkAction::make('bulk_push')
            ->label('Push ke Device')
            ->icon('heroicon-o-arrow-up-tray')
            ->color('success')
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
            ->action(function (Collection $records, array $data) use ($type): void {
                $results = ['success' => [], 'failed' => []];
                $relation = $type === 'student' ? 'students' : 'employees';

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
                            $success = $device->getClient()->setUserInfo(
                                pin: $record->pin,
                                name: $record->name,
                            );

                            if ($success) {
                                $device->$relation()->syncWithoutDetaching([
                                    $record->id => ['pushed_at' => now()],
                                ]);
                                $results['success'][] = $record->name;
                            } else {
                                $results['failed'][] = $record->name;
                            }
                        } catch (\Throwable $e) {
                            $results['failed'][] = $record->name.' ('.$e->getMessage().')';
                        }
                    }
                }

                if (! empty($results['success'])) {
                    Notification::make()
                        ->title(count($results['success']).' data berhasil di-push')
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
