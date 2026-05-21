<?php

namespace App\Filament\Resources\FingerprintDevices\RelationManagers;

use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

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
                    ->label('Push')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->action(function (Student $record): void {
                        $device = $this->getOwnerRecord();
                        try {
                            $success = $device->getClient()->setUserInfo(
                                pin: $record->pin,
                                name: $record->name,
                            );

                            if ($success) {
                                $device->students()->updateExistingPivot($record->id, [
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
                            $success = $device->getClient()->deleteUser($record->pin);

                            if ($success) {
                                $device->students()->detach($record->id);

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
