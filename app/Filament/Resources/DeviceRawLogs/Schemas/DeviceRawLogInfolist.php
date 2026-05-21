<?php

namespace App\Filament\Resources\DeviceRawLogs\Schemas;

use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeviceRawLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Waktu')
                            ->dateTime(),

                        TextEntry::make('device_serial_number')
                            ->label('Serial ADMS')
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('fingerprintDevice.name')
                            ->label('Mesin')
                            ->placeholder('-'),

                        TextEntry::make('method')
                            ->label('Method')
                            ->badge(),

                        TextEntry::make('endpoint')
                            ->label('Endpoint')
                            ->copyable(),

                        TextEntry::make('table_name')
                            ->label('Table')
                            ->badge()
                            ->placeholder('-'),

                        TextEntry::make('processed_count')
                            ->label('Diproses')
                            ->numeric(),
                    ]),

                Section::make('Query')
                    ->schema([
                        CodeEntry::make('query_payload')
                            ->label('Query Payload')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Body')
                    ->schema([
                        CodeEntry::make('body_payload')
                            ->label('Body Payload')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
