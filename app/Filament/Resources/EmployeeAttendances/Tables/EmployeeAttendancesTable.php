<?php

namespace App\Filament\Resources\EmployeeAttendances\Tables;

use App\Filament\Exports\EmployeeAttendanceExporter;
use App\Filament\Imports\EmployeeAttendanceImporter;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EmployeeAttendancesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attendable.nip')
                    ->label('NIP')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendable.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('attendable.position')
                    ->label('Jabatan')
                    ->placeholder('-'),

                TextColumn::make('date')
                    ->label('Tanggal')
                    ->date()
                    ->sortable(),

                TextColumn::make('check_in')
                    ->time()
                    ->label('Masuk'),

                TextColumn::make('check_out')
                    ->time()
                    ->label('Keluar'),

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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'present' => 'Hadir',
                        'absent' => 'Tidak Hadir',
                        'late' => 'Terlambat',
                        'permitted' => 'Izin',
                    ]),

                SelectFilter::make('source')
                    ->label('Sumber')
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
                ImportAction::make()->importer(EmployeeAttendanceImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(EmployeeAttendanceExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ])
            ->defaultSort('date', 'desc');
    }
}
