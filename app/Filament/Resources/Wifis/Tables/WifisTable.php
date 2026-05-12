<?php

namespace App\Filament\Resources\Wifis\Tables;

use App\Filament\Exports\WifiExporter;
use App\Filament\Imports\WifiImporter;
use App\Models\Wifi;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ImportAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WifisTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('ssid')
                    ->label('SSID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ip_address')
                    ->label('IP Address'),

                TextColumn::make('router_type')
                    ->label('Tipe Router')
                    ->badge(),

                TextColumn::make('link')
                    ->label('Link Admin')
                    ->url(fn(Wifi $record): ?string => $record->link)
                    ->openUrlInNewTab(),

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
                ImportAction::make()->importer(WifiImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label("Upload data"),
                ExportAction::make()->exporter(WifiExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label("Download data"),
            ]);
    }
}
