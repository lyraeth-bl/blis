<?php

namespace App\Filament\Resources\FingerprintDevices\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FingerprintDeviceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Status Mesin')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('connection_status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'active' => 'Aktif',
                                default => 'Tidak Aktif',
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                default => 'danger',
                            }),

                        TextEntry::make('last_seen_at')
                            ->label('Terakhir Online')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
