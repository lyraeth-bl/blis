<?php

namespace App\Filament\Resources\Websites\Tables;

use App\Filament\Exports\WebsiteExporter;
use App\Filament\Imports\WebsiteImporter;
use App\Models\Website;
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

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('url')
                    ->label('URL/Domain')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Website $record): ?string => $record->url)
                    ->openUrlInNewTab(),

                TextColumn::make('username')
                    ->label('Username/Email'),

                TextColumn::make('category')
                    ->label('Kategori')
                    ->badge(),

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
                ImportAction::make()->importer(WebsiteImporter::class)->color(Color::Blue)->icon(Heroicon::ArrowUpTray)->label('Upload data'),
                ExportAction::make()->exporter(WebsiteExporter::class)->color(Color::Amber)->icon(Heroicon::ArrowDownTray)->label('Download data'),
            ]);
    }
}
