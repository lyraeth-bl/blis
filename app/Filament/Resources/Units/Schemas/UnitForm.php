<?php

namespace App\Filament\Resources\Units\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UnitForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Unit')->schema([
                    TextInput::make('code')
                        ->label('Kode')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50),
                    TextInput::make('name')
                        ->label('Nama Unit')
                        ->required()
                        ->maxLength(100),
                    TextInput::make('campus')
                        ->label('Lokasi')
                        ->required()
                        ->maxLength(100),
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
