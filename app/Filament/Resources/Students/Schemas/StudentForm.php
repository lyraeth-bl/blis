<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Siswa')->schema([
                    TextInput::make('nis')
                        ->label('NIS')
                        ->required()
                        ->maxLength(9)
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),

                    Select::make('unit')
                        ->label('Unit')
                        ->options([
                            'SMAKT' => 'SMAKT',
                            'SMKKT' => 'SMKKT',
                        ])
                        ->native(false)
                        ->required(),

                    TextInput::make('class')
                        ->label('Kelas')
                        ->hint('Optional'),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
