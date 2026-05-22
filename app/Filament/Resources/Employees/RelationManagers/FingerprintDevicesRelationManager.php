<?php

namespace App\Filament\Resources\Employees\RelationManagers;

use App\Filament\Resources\Employees\EmployeeResource;
use App\Models\FingerprintDevice;
use App\Services\AdmsCommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

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
                    ->label('Push ADMS ke Device')
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
                                app(AdmsCommandService::class)->queueUpdateUser(
                                    device: $device,
                                    attendable: $employee,
                                    requestedBy: Auth::user(),
                                );

                                $device->employees()->syncWithoutDetaching([
                                    $employee->id => ['pushed_at' => null],
                                ]);

                                $results['success'][] = $device->name;
                            } catch (\Throwable $e) {
                                $results['failed'][] = $device->name.' ('.$e->getMessage().')';
                            }
                        }

                        if (! empty($results['success'])) {
                            Notification::make()
                                ->title('Command push ADMS dibuat untuk: '.implode(', ', $results['success']))
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
                            app(AdmsCommandService::class)->queueDeleteUser(
                                device: $record,
                                attendable: $employee,
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command hapus dari {$record->name} dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('query_user')
                    ->label('Query User')
                    ->icon('heroicon-o-magnifying-glass')
                    ->color('gray')
                    ->action(function (FingerprintDevice $record): void {
                        $employee = $this->getOwnerRecord();

                        try {
                            app(AdmsCommandService::class)->queueQueryUser(
                                device: $record,
                                attendable: $employee,
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command query ke {$record->name} dibuat")
                                ->success()
                                ->send();
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
