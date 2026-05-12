<?php

namespace App\Filament\Resources\Wifis\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WifiInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Jaringan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('ssid')
                            ->label('SSID'),

                        TextEntry::make('location')
                            ->label('Lokasi'),

                        TextEntry::make('ip_address')
                            ->label('IP Address'),

                        TextEntry::make('router_type')
                            ->label('Tipe Router')
                            ->badge(),

                        TextEntry::make('password')
                            ->label('Password Wifi')
                            ->placeholder('-')
                            ->copyable(),
                    ]),

                Section::make('Admin Router')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('admin_username')
                            ->label('Username Admin')
                            ->placeholder('-'),

                        TextEntry::make('admin_password')
                            ->label('Password Admin')
                            ->placeholder('-')
                            ->copyable(),

                        TextEntry::make('link')
                            ->label('Link Admin')
                            ->placeholder('-')
                            ->url(fn($record) => $record->link)
                            ->openUrlInNewTab(),
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
