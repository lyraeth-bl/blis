<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\Employee;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EmployeesRelationManager extends RelationManager
{
    protected static string $relationship = 'employees';

    protected static ?string $title = 'Karyawan di Device';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'employee'
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->employees()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                TextColumn::make('position')
                    ->label('Jabatan')
                    ->searchable(),

                TextColumn::make('pivot.pushed_at')
                    ->label('Dikirim ke Device')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Action::make('attach')
                    ->label('Tambah Karyawan')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('employee_id')
                            ->label('Karyawan')
                            ->options(Employee::query()->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $this->getOwnerRecord()->employees()->syncWithoutDetaching([
                            $data['employee_id'] => ['pushed_at' => null],
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('push')
                    ->label('Push')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(function (Employee $record): void {
                        $device = $this->getOwnerRecord();
                        try {
                            $success = $device->getClient()->setUserInfo(
                                pin: $record->pin,
                                name: $record->name,
                            );

                            if ($success) {
                                $device->employees()->updateExistingPivot($record->id, [
                                    'pushed_at' => now(),
                                ]);

                                Notification::make()
                                    ->title("Berhasil push {$record->name}")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title("Gagal push {$record->name}")
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('delete_from_device')
                    ->label('Hapus dari Mesin')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Employee $record): void {
                        $device = $this->getOwnerRecord();
                        try {
                            $success = $device->getClient()->deleteUser($record->pin);

                            if ($success) {
                                $device->employees()->detach($record->id);

                                Notification::make()
                                    ->title("Berhasil hapus {$record->name} dari mesin")
                                    ->success()
                                    ->send();
                            } else {
                                Notification::make()
                                    ->title("Gagal hapus {$record->name}")
                                    ->danger()
                                    ->send();
                            }
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->emptyStateIcon(Heroicon::UserGroup)
            ->emptyStateHeading('Belum ada karyawan di device ini')
            ->emptyStateDescription('Karyawan yang sudah terdaftar di mesin fingerprint ini akan muncul di sini.');
    }
}
