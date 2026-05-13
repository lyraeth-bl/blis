<?php

namespace App\Filament\Resources\Employees\Tables;

use App\Filament\Actions\BulkActions\DeleteFromDeviceBulkAction;
use App\Filament\Actions\BulkActions\PushToDeviceBulkAction;
use App\Filament\Exports\EmployeeExporter;
use App\Filament\Imports\EmployeeImporter;
use App\Models\FingerprintDevice;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

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
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
                PushToDeviceBulkAction::make('employee'),
                DeleteFromDeviceBulkAction::make('employee'),
                ImportAction::make()->importer(EmployeeImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(EmployeeExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ])
            ->emptyStateIcon(Heroicon::UserGroup)
            ->emptyStateHeading('Karyawan masih kosong nih')
            ->emptyStateDescription('Daftarin karyawan dulu biar datanya muncul di halaman ini.');

    }
}
