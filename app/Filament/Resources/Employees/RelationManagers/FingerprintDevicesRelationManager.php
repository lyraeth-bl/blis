<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\FingerprintDevice;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FingerprintDevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'fingerprintDevices';

    protected static ?string $relatedResource = EmployeeResource::class;

    protected static ?string $title = 'Fingerprint Device';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Device')
                    ->searchable(),

                TextColumn::make('location')
                    ->label('Lokasi'),

                TextColumn::make('units.display_name')
                    ->label('Unit')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('-'),

                TextColumn::make('ip_address')
                    ->label('IP Address'),

                TextColumn::make('pivot.pushed_at')
                    ->label('Terakhir Di-push')
                    ->dateTime()
                    ->placeholder('Belum pernah'),
            ])
            ->headerActions([
                Action::make('push_to_devices')
                    ->label('Push ke Device')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        CheckboxList::make('device_ids')
                            ->label('Pilih Device')
                            ->options(fn (): array => FingerprintDevice::query()
                                ->where('type', 'employee')
                                ->whereHas('units', fn ($query) => $query->whereKey($this->getOwnerRecord()->unit_id))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $employee = $this->getOwnerRecord();
                        $results = ['success' => [], 'failed' => []];

                        foreach ($data['device_ids'] as $deviceId) {
                            $device = FingerprintDevice::find($deviceId);
                            if (! $device) {
                                continue;
                            }

                            if (! $device->supportsUnit($employee->unit_id)) {
                                $results['failed'][] = $device->name.' (unit berbeda)';

                                continue;
                            }

                            try {
                                $success = $device->getClient()->setUserInfo(
                                    pin: $employee->pin,
                                    name: $employee->name,
                                );

                                if ($success) {
                                    $device->employees()->syncWithoutDetaching([
                                        $employee->id => ['pushed_at' => now()],
                                    ]);
                                    $results['success'][] = $device->name;
                                } else {
                                    $results['failed'][] = $device->name;
                                }
                            } catch (\Throwable $e) {
                                $results['failed'][] = $device->name.' ('.$e->getMessage().')';
                            }
                        }

                        if (! empty($results['success'])) {
                            Notification::make()
                                ->title('Berhasil push ke: '.implode(', ', $results['success']))
                                ->success()
                                ->send();
                        }

                        if (! empty($results['failed'])) {
                            Notification::make()
                                ->title('Gagal push ke: '.implode(', ', $results['failed']))
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                Action::make('delete_from_device')
                    ->label('Hapus dari Mesin')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (FingerprintDevice $record): void {
                        $employee = $this->getOwnerRecord();
                        try {
                            $success = $record->getClient()->deleteUser($employee->pin);

                            if ($success) {
                                $record->employees()->detach($employee->id);

                                Notification::make()
                                    ->title("Berhasil hapus dari {$record->name}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title("Gagal hapus dari {$record->name}")
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ]);
    }
}
