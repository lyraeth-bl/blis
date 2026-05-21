<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Website')->schema([
                    TextInput::make('name')
                        ->label('Nama Website')
                        ->required(),

                    TextInput::make('url')
                        ->label('URL/Domain')
                        ->required()
                        ->url(),

                    TextInput::make('username')
                        ->label('Username/Email')
                        ->required(),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable(),

                    Select::make('category')
                        ->label('Kategori')
                        ->native(false)
                        ->options([
                            'Business' => 'Business',
                            'Personal' => 'Personal',
                            'Social Media' => 'Social Media',
                            'E-commerce' => 'E-commerce',
                            'Other' => 'Other',
                        ]),
                ]),

                Section::make('Lainnya')->schema([
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),
                ]),
            ]);
    }
}
