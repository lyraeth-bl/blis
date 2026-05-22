<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\Student;
use App\Services\AdmsCommandService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
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
                ActionGroup::make([
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

                    Action::make('delete_fingerprint_template')
                        ->label('Hapus Finger')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            TextInput::make('fid')
                                ->label('FID')
                                ->numeric()
                                ->minValue(0),
                        ])
                        ->action(function (Student $record, array $data): void {
                            try {
                                app(AdmsCommandService::class)->queueDeleteFingerprintTemplate(
                                    device: $this->getOwnerRecord(),
                                    attendable: $record,
                                    fingerId: filled($data['fid'] ?? null) ? (int) $data['fid'] : null,
                                    requestedBy: Auth::user(),
                                );

                                Notification::make()
                                    ->title("Command hapus finger {$record->name} dibuat")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Error: '.$e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_push')
                        ->label('Push ADMS')
                        ->icon('heroicon-o-arrow-up-tray')
                        ->color('success')
                        ->action(function (Collection $records): void {
                            $results = ['success' => [], 'failed' => []];
                            $device = $this->getOwnerRecord();

                            foreach ($records as $record) {
                                try {
                                    app(AdmsCommandService::class)->queueUpdateUser(
                                        device: $device,
                                        attendable: $record,
                                        requestedBy: Auth::user(),
                                    );

                                    $device->students()->syncWithoutDetaching([
                                        $record->id => ['pushed_at' => null],
                                    ]);

                                    $results['success'][] = $record->name;
                                } catch (\Throwable $e) {
                                    $results['failed'][] = $record->name.' ('.$e->getMessage().')';
                                }
                            }

                            $this->sendBulkCommandNotifications($results, 'push ADMS');
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_delete_from_device')
                        ->label('Hapus dari Mesin')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $results = ['success' => [], 'failed' => []];
                            $device = $this->getOwnerRecord();

                            foreach ($records as $record) {
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

                            $this->sendBulkCommandNotifications($results, 'hapus dari mesin');
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_delete_fingerprint_template')
                        ->label('Hapus Finger')
                        ->icon('heroicon-o-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->form([
                            TextInput::make('fid')
                                ->label('FID')
                                ->numeric()
                                ->minValue(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $this->bulkQueueDeleteFingerprintTemplate($records, $data);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_query_user')
                        ->label('Query User')
                        ->icon('heroicon-o-magnifying-glass')
                        ->color('gray')
                        ->action(function (Collection $records): void {
                            $this->bulkQueueQueryUser($records);
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('bulk_query_fingerprint_template')
                        ->label('Query Finger')
                        ->icon('heroicon-o-finger-print')
                        ->color('gray')
                        ->form([
                            TextInput::make('fid')
                                ->label('FID')
                                ->numeric()
                                ->minValue(0),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $this->bulkQueueQueryFingerprintTemplate($records, $data);
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::Users)
            ->emptyStateHeading('Belum ada siswa di device ini')
            ->emptyStateDescription('Siswa yang sudah terdaftar di mesin fingerprint ini akan muncul di sini.');
    }

    private function bulkQueueQueryUser(Collection $records): void
    {
        $results = ['success' => [], 'failed' => []];
        $device = $this->getOwnerRecord();

        foreach ($records as $record) {
            try {
                app(AdmsCommandService::class)->queueQueryUser(
                    device: $device,
                    attendable: $record,
                    requestedBy: Auth::user(),
                );

                $results['success'][] = $record->name;
            } catch (\Throwable $e) {
                $results['failed'][] = $record->name.' ('.$e->getMessage().')';
            }
        }

        $this->sendBulkCommandNotifications($results, 'query user');
    }

    private function bulkQueueDeleteFingerprintTemplate(Collection $records, array $data): void
    {
        $results = ['success' => [], 'failed' => []];
        $device = $this->getOwnerRecord();
        $fingerId = filled($data['fid'] ?? null)
            ? (int) $data['fid']
            : null;

        foreach ($records as $record) {
            try {
                app(AdmsCommandService::class)->queueDeleteFingerprintTemplate(
                    device: $device,
                    attendable: $record,
                    fingerId: $fingerId,
                    requestedBy: Auth::user(),
                );

                $results['success'][] = $record->name;
            } catch (\Throwable $e) {
                $results['failed'][] = $record->name.' ('.$e->getMessage().')';
            }
        }

        $this->sendBulkCommandNotifications($results, 'hapus finger');
    }

    private function bulkQueueQueryFingerprintTemplate(Collection $records, array $data): void
    {
        $results = ['success' => [], 'failed' => []];
        $device = $this->getOwnerRecord();
        $fingerId = filled($data['fid'] ?? null)
            ? (int) $data['fid']
            : null;

        foreach ($records as $record) {
            try {
                app(AdmsCommandService::class)->queueQueryFingerprintTemplate(
                    device: $device,
                    attendable: $record,
                    fingerId: $fingerId,
                    requestedBy: Auth::user(),
                );

                $results['success'][] = $record->name;
            } catch (\Throwable $e) {
                $results['failed'][] = $record->name.' ('.$e->getMessage().')';
            }
        }

        $this->sendBulkCommandNotifications($results, 'query finger');
    }

    /**
     * @param  array{success: array<int, string>, failed: array<int, string>}  $results
     */
    private function sendBulkCommandNotifications(array $results, string $actionLabel): void
    {
        if (! empty($results['success'])) {
            Notification::make()
                ->title(count($results['success'])." command {$actionLabel} dibuat")
                ->success()
                ->send();
        }

        if (! empty($results['failed'])) {
            Notification::make()
                ->title(count($results['failed']).' data gagal: '.implode(', ', $results['failed']))
                ->danger()
                ->send();
        }
    }
}
