<?php

namespace App\Filament\Actions\BulkActions;

use App\Models\FingerprintDevice;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection;

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
                    ->options(
                        FingerprintDevice::where('type', $type)
                            ->pluck('name', 'id')
                    )
                    ->required(),
            ])
            ->action(function (Collection $records, array $data) use ($type): void {
                $results = ['success' => [], 'failed' => []];
                $relation = $type === 'student' ? 'students' : 'employees';

                foreach ($data['device_ids'] as $deviceId) {
                    $device = FingerprintDevice::find($deviceId);
                    if (!$device)
                        continue;

                    foreach ($records as $record) {
                        try {
                            $success = $device->getClient()->deleteUser($record->pin);

                            if ($success) {
                                $device->$relation()->detach($record->id);
                                $results['success'][] = $record->name;
                            } else {
                                $results['failed'][] = $record->name;
                            }
                        } catch (\Throwable $e) {
                            $results['failed'][] = $record->name . ' (' . $e->getMessage() . ')';
                        }
                    }
                }

                if (!empty($results['success'])) {
                    Notification::make()
                        ->title(count($results['success']) . ' data berhasil dihapus dari device')
                        ->success()
                        ->send();
                }

                if (!empty($results['failed'])) {
                    Notification::make()
                        ->title(count($results['failed']) . ' data gagal: ' . implode(', ', $results['failed']))
                        ->danger()
                        ->send();
                }
            })
            ->deselectRecordsAfterCompletion();
    }
}