<?php

namespace App\Filament\Resources\SpoPostLogs\Schemas;

use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SpoPostLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Ringkasan')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'success' => 'success',
                                'failed' => 'danger',
                                'skipped' => 'gray',
                                default => 'warning',
                            }),

                        TextEntry::make('endpoint_type')
                            ->label('Endpoint')
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'attendance' => 'Absensi',
                                'notification' => 'Notifikasi',
                                default => $state,
                            }),

                        TextEntry::make('field')
                            ->label('Aksi')
                            ->placeholder('-'),

                        TextEntry::make('fingerprintDevice.name')
                            ->label('Mesin')
                            ->placeholder('-'),

                        TextEntry::make('attendable.name')
                            ->label('Nama')
                            ->placeholder('-'),

                        TextEntry::make('attendanceFetchLog.sync_id')
                            ->label('Sync ID')
                            ->copyable()
                            ->placeholder('-'),

                        TextEntry::make('url')
                            ->label('URL')
                            ->copyable()
                            ->placeholder('-')
                            ->columnSpanFull(),

                        TextEntry::make('http_status')
                            ->label('HTTP Status')
                            ->placeholder('-'),

                        TextEntry::make('attempted_at')
                            ->label('Waktu')
                            ->dateTime(),
                    ]),

                Section::make('Error atau Skip')
                    ->schema([
                        TextEntry::make('skipped_reason')
                            ->label('Alasan Skip')
                            ->placeholder('-'),

                        TextEntry::make('error_message')
                            ->label('Error')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Payload')
                    ->schema([
                        CodeEntry::make('payload')
                            ->label('Payload')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Response')
                    ->schema([
                        CodeEntry::make('response_body')
                            ->label('Response Body')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
