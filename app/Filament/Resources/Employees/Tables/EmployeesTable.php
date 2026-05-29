<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Actions\BulkActions\DeleteFromDeviceBulkAction;
use App\Filament\Actions\BulkActions\PushToDeviceBulkAction;
use App\Filament\Exports\EmployeeExporter;
use App\Filament\Imports\EmployeeImporter;
use App\Models\Unit;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
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

                TextColumn::make('accessible_units')
                    ->label('Unit')
                    ->badge()
                    ->state(fn ($record): string => $record->accessibleUnitsLabel())
                    ->placeholder('-'),

                TextColumn::make('fingerprintDevices.name')
                    ->label('Terdaftar di Device')
                    ->badge()
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit_id')
                    ->label('Unit')
                    ->native(false)
                    ->options(fn (): array => Unit::query()
                        ->orderBy('name')
                        ->orderBy('campus')
                        ->get(['id', 'name', 'campus'])
                        ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->display_name])
                        ->all())
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, int|string $unitId): Builder => $query->where(function (Builder $query) use ($unitId): void {
                            $query->where('unit_id', $unitId)
                                ->orWhereHas('units', fn (Builder $query): Builder => $query->whereKey($unitId));
                        }),
                    ))
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                PushToDeviceBulkAction::make('employee')
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
                DeleteFromDeviceBulkAction::make('employee')
                    ->visible(fn (): bool => Auth::user()?->isAdmin() ?? false),
                ImportAction::make()->importer(EmployeeImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(EmployeeExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ])
            ->emptyStateIcon(Heroicon::UserGroup)
            ->emptyStateHeading('Karyawan masih kosong nih')
            ->emptyStateDescription('Daftarin karyawan dulu biar datanya muncul di halaman ini.');

    }
}
