<?php

namespace App\Filament\Resources\Units\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UnitsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Unit')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('campus')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('students_count')
                    ->label('Siswa')
                    ->counts('students')
                    ->sortable(),
                TextColumn::make('employees_count')
                    ->label('Karyawan')
                    ->counts('employees')
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::BuildingOffice)
            ->emptyStateHeading('Unit masih kosong');
    }
}
