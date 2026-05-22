<?php

namespace App\Filament\Resources\PicketSchedules\Tables;

use App\Filament\Imports\PicketScheduleImporter;
use App\Models\Unit;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PicketSchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee.name')
                    ->label('Guru / Staff')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unitModel.display_name')
                    ->label('Unit'),
                TextColumn::make('day_label')
                    ->label('Hari')
                    ->badge(),
                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->time('H:i'),
                TextColumn::make('ends_at')
                    ->label('Selesai')
                    ->time('H:i'),
                TextColumn::make('effective_from')
                    ->label('Dari')
                    ->date()
                    ->sortable(),
                TextColumn::make('effective_until')
                    ->label('Sampai')
                    ->date()
                    ->sortable(),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->options(fn (): array => Unit::query()
                        ->orderBy('campus')
                        ->orderBy('name')
                        ->get()
                        ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->display_name])
                        ->all()),
                SelectFilter::make('day_of_week')
                    ->label('Hari')
                    ->options([
                        1 => 'Senin',
                        2 => 'Selasa',
                        3 => 'Rabu',
                        4 => 'Kamis',
                        5 => 'Jumat',
                        6 => 'Sabtu',
                        7 => 'Minggu',
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
                ImportAction::make()
                    ->importer(PicketScheduleImporter::class)
                    ->color(Color::Blue)
                    ->icon(Heroicon::ArrowUpTray)
                    ->label('Upload jadwal'),
            ]);
    }
}
