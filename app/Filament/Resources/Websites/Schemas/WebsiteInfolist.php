<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Website')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama Website'),

                        TextEntry::make('url')
                            ->label('URL/Domain')
                            ->url(fn ($record) => $record->url)
                            ->openUrlInNewTab(),

                        // TextEntry::make('username')
                        //     ->label('Username/Email'),

                        // TextEntry::make('password')
                        //     ->label('Password')
                        //     ->placeholder('-')
                        //     ->copyable(),

                        TextEntry::make('category')
                            ->label('Kategori')
                            ->badge(),

                        TextEntry::make('unitModel.display_name')
                            ->label('Unit')
                            ->placeholder('Semua unit'),

                        TextEntry::make('is_private')
                            ->label('Visibilitas')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Private' : 'Public')
                            ->badge(),
                    ]),

                Section::make('Lainnya')
                    ->schema([
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Timestamps')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime(),
                    ]),
            ]);
    }
}
