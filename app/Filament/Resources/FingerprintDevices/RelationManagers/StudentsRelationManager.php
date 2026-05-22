<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\Student;
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

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'students';

    protected static ?string $title = 'Siswa di Device';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->type === 'student'
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->students()->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unitModel.display_name')
                    ->label('Unit')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('class')
                    ->label('Kelas')
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
                    ->label('Tambah Siswa')
                    ->icon('heroicon-o-plus')
                    ->form([
                        Select::make('student_id')
                            ->label('Siswa')
                            ->options(fn (): array => Student::query()
                                ->whereIn('unit_id', $this->getOwnerRecord()->units()->pluck('units.id'))
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (array $data): void {
                        $student = Student::query()->find($data['student_id']);

                        abort_unless(
                            $student !== null && $this->getOwnerRecord()->supportsUnit($student->unit_id),
                            403,
                        );

                        $this->getOwnerRecord()->students()->syncWithoutDetaching([
                            $data['student_id'] => ['pushed_at' => null],
                        ]);
                    }),
            ])
            ->recordActions([
                Action::make('push')
                    ->label('Push ADMS')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(function (Student $record): void {
                        $device = $this->getOwnerRecord();
                        try {
                            app(AdmsCommandService::class)->queueUpdateUser(
                                device: $device,
                                attendable: $record,
                                requestedBy: Auth::user(),
                            );

                            $device->students()->syncWithoutDetaching([
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
                    ->action(function (Student $record): void {
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
                    ->action(function (Student $record): void {
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
                    ->action(function (Student $record, array $data): void {
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
                    ->action(function (Student $record, array $data): void {
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
            ->emptyStateIcon(Heroicon::Users)
            ->emptyStateHeading('Belum ada siswa di device ini')
            ->emptyStateDescription('Siswa yang sudah terdaftar di mesin fingerprint ini akan muncul di sini.');
    }
}
