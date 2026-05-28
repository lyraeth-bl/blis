<?php

namespace App\Filament\Resources\Wifis\Schemas;

use App\Models\Wifi;
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

                        TextEntry::make('units')
                            ->label('Unit')
                            ->state(fn (Wifi $record): string => $record->units->pluck('display_name')->join(', ') ?: 'Semua unit'),

                        TextEntry::make('is_private')
                            ->label('Visibilitas')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Private' : 'Public')
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
                            ->url(fn ($record) => $record->link)
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
