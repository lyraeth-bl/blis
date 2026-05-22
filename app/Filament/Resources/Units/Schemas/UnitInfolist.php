<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Unit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode')
                            ->badge()
                            ->copyable(),
                        TextEntry::make('name')
                            ->label('Nama Unit')
                            ->weight('bold'),
                        TextEntry::make('campus')
                            ->label('Lokasi'),
                    ]),

                Section::make('Catatan')
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
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
