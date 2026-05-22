<?php

namespace App\Filament\Resources\StudentAttendances\Tables;

use App\Filament\Exports\StudentAttendanceExporter;
use App\Filament\Imports\StudentAttendanceImporter;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class StudentAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attendable.nis')
                    ->label('NIS')
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->whereHasMorph(
                        'attendable',
                        [Student::class],
                        fn(Builder $query): Builder => $query->where('nis', 'like', "%{$search}%"),
                    ))
                    ->sortable(),

                TextColumn::make('attendable.name')
                    ->label('Nama')
                    ->searchable(query: fn(Builder $query, string $search): Builder => $query->whereHasMorph(
                        'attendable',
                        [Student::class],
                        fn(Builder $query): Builder => $query->where('name', 'like', "%{$search}%"),
                    ))
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->state(fn(Attendance $record): string => $record->attendable?->unitModel?->display_name ?? $record->attendable?->unit ?? '-'),

                TextColumn::make('attendable.class')
                    ->label('Kelas'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('check_in')
                    ->label('Masuk')
                    ->time(),

                TextColumn::make('check_out')
                    ->label('Keluar')
                    ->time(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'present' => 'success',
                        'late' => 'warning',
                        'permitted' => 'info',
                        'absent' => 'danger',
                    }),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'fingerprint' => 'success',
                        'manual' => 'warning',
                    }),

                TextColumn::make('fingerprintDevice.name')
                    ->label('Device')
                    ->placeholder('-'),

                TextColumn::make('edited_by')
                    ->label('Diedit oleh')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('edited_at')
                    ->label('Diedit pada')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('date')
                    ->label('Tanggal')
                    ->schema([
                        DatePicker::make('from_date')
                            ->native(false)
                            ->label('Dari tanggal'),
                        DatePicker::make('to_date')
                            ->native(false)
                            ->label('Sampai tanggal'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when(
                            $data['from_date'] ?? null,
                            fn(Builder $query, string $date): Builder => $query->whereDate('date', '>=', $date),
                        )
                        ->when(
                            $data['to_date'] ?? null,
                            fn(Builder $query, string $date): Builder => $query->whereDate('date', '<=', $date),
                        )),

                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->native(false)
                    ->options(fn(): array => Unit::query()
                        ->orderBy('name')
                        ->orderBy('campus')
                        ->get(['id', 'name', 'campus'])
                        ->mapWithKeys(fn(Unit $unit): array => [$unit->id => $unit->display_name])
                        ->all())
                    ->query(fn(Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn(Builder $query, int|string $unitId): Builder => $query->whereHasMorph(
                            'attendable',
                            [Student::class],
                            fn(Builder $query): Builder => $query->where('unit_id', $unitId),
                        ),
                    ))
                    ->searchable()
                    ->preload()
                    ->visible(fn(): bool => Auth::user()?->isAdmin() ?? false),

                SelectFilter::make('status')
                    ->label('Status')
                    ->native(false)
                    ->options([
                        'present' => 'Hadir',
                        'absent' => 'Tidak Hadir',
                        'late' => 'Terlambat',
                        'permitted' => 'Izin',
                    ]),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->native(false)
                    ->options([
                        'manual' => 'Manual',
                        'fingerprint' => 'Fingerprint',
                    ]),

            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                ImportAction::make()->importer(StudentAttendanceImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(StudentAttendanceExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ])
            ->defaultSort('date', 'desc');

    }
}
