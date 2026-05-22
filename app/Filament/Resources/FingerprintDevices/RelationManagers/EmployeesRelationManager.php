<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\Employee;
use App\Services\AdmsCommandService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

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

                TextColumn::make('unitModel.display_name')
                    ->label('Unit')
                    ->badge()
                    ->placeholder('-'),

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
                            ->options(fn (): array => Employee::query()
                                ->whereIn('unit_id', $this->getOwnerRecord()->units()->pluck('units.id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $employee = Employee::query()->find($data['employee_id']);

                        abort_unless(
                            $employee !== null && $this->getOwnerRecord()->supportsUnit($employee->unit_id),
                            403,
                        );

                        $this->getOwnerRecord()->employees()->syncWithoutDetaching([
                            $data['employee_id'] => ['pushed_at' => null],
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('push')
                    ->label('Push ADMS')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(function (Employee $record): void {
                        $device = $this->getOwnerRecord();
                        try {
                            app(AdmsCommandService::class)->queueUpdateUser(
                                device: $device,
                                attendable: $record,
                                requestedBy: Auth::user(),
                            );

                            $device->employees()->syncWithoutDetaching([
                                $record->id => ['pushed_at' => null],
                            ]);

                            Notification::make()
                                ->title("Command push {$record->name} dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
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
                            app(AdmsCommandService::class)->queueDeleteUser(
                                device: $device,
                                attendable: $record,
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command hapus {$record->name} dibuat")
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
                    ->action(function (Employee $record): void {
                        try {
                            app(AdmsCommandService::class)->queueQueryUser(
                                device: $this->getOwnerRecord(),
                                attendable: $record,
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command query {$record->name} dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('push_fingerprint_template')
                    ->label('Upload Finger')
                    ->icon('heroicon-o-finger-print')
                    ->color('info')
                    ->form([
                        TextInput::make('fid')
                            ->label('FID')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->required(),

                        Select::make('valid')
                            ->label('Valid')
                            ->options([
                                '1' => 'Valid',
                                '0' => 'Tidak valid',
                            ])
                            ->default('1')
                            ->required(),

                        Textarea::make('tmp')
                            ->label('TMP Base64')
                            ->rows(6)
                            ->required(),
                    ])
                    ->action(function (Employee $record, array $data): void {
                        try {
                            app(AdmsCommandService::class)->queueUpdateFingerprintTemplate(
                                device: $this->getOwnerRecord(),
                                attendable: $record,
                                fingerId: (int) $data['fid'],
                                template: (string) $data['tmp'],
                                valid: (string) $data['valid'] === '1',
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command upload finger {$record->name} dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Action::make('query_fingerprint_template')
                    ->label('Query Finger')
                    ->icon('heroicon-o-finger-print')
                    ->color('gray')
                    ->form([
                        TextInput::make('fid')
                            ->label('FID')
                            ->numeric()
                            ->minValue(0),
                    ])
                    ->action(function (Employee $record, array $data): void {
                        try {
                            $fingerId = filled($data['fid'] ?? null)
                                ? (int) $data['fid']
                                : null;

                            app(AdmsCommandService::class)->queueQueryFingerprintTemplate(
                                device: $this->getOwnerRecord(),
                                attendable: $record,
                                fingerId: $fingerId,
                                requestedBy: Auth::user(),
                            );

                            Notification::make()
                                ->title("Command query finger {$record->name} dibuat")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error: '.$e->getMessage())
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
