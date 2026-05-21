<?php

namespace App\Filament\Resources\Wifis\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WifiForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jaringan')->schema([
                    TextInput::make('ssid')
                        ->label('SSID (Nama Wifi)')
                        ->required(),

                    TextInput::make('location')
                        ->label('Lokasi')
                        ->required(),

                    TextInput::make('ip_address')
                        ->label('IP Address')
                        ->required()
                        ->ipv4()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state) {
                                $set('link', "http://{$state}");
                            }
                        }),

                    TextInput::make('password')
                        ->label('Password Wifi')
                        ->password()
                        ->revealable(),

                    Select::make('router_type')
                        ->label('Tipe Router')
                        ->native(false)
                        ->options([
                            'tenda' => 'Tenda',
                            'tp-link' => 'TP-Link',
                            'ruijie' => 'Ruijie',
                        ])
                        ->required(),
                ]),

                Section::make('Admin Router')->schema([
                    TextInput::make('admin_username')
                        ->label('Username Admin'),

                    TextInput::make('admin_password')
                        ->label('Password Admin')
                        ->password()
                        ->revealable(),

                    TextInput::make('link')
                        ->label('Link Admin')
                        ->url()
                        ->placeholder('Otomatis dari IP Address')
                        ->default(null)
                        ->dehydrated()
                        ->disabledOn('create')
                        ->afterStateHydrated(function ($state, callable $set, $record) {
                            if ($record && $record->ip_address) {
                                $set('link', "http://{$record->ip_address}");
                            }
                        })
                        ->reactive(),
                ]),

                Section::make('Lainnya')->schema([
                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),
                ]),
            ]);
    }
}
