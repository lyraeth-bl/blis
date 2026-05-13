<?php

namespace App\Filament\Resources\Students\Tables;

use App\Filament\Actions\BulkActions\DeleteFromDeviceBulkAction;
use App\Filament\Actions\BulkActions\PushToDeviceBulkAction;
use App\Filament\Exports\StudentExporter;
use App\Filament\Imports\StudentImporter;
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

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('unit')
                    ->label('Unit')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'SMAKT' => 'info',
                        'SMKKT' => 'warning',
                    }),

                TextColumn::make('class')
                    ->label('Kelas')
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
                PushToDeviceBulkAction::make('student'),
                DeleteFromDeviceBulkAction::make('student'),
                ImportAction::make()->importer(StudentImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(StudentExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ])
            ->emptyStateIcon(Heroicon::Users)
            ->emptyStateHeading('Siswa masih kosong nih')
            ->emptyStateDescription('Daftarin siswa dulu biar datanya muncul di halaman ini.');
    }
}
