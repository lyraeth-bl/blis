<?php

namespace App\Filament\Resources\DeviceRawLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeviceRawLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('device_serial_number')
                    ->label('Serial ADMS')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('fingerprintDevice.name')
                    ->label('Mesin')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('method')
                    ->label('Method')
                    ->badge()
                    ->sortable(),

                TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->searchable(),

                TextColumn::make('table_name')
                    ->label('Table')
                    ->badge()
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('processed_count')
                    ->label('Diproses')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('body_payload')
                    ->label('Body')
                    ->limit(60)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('method')
                    ->options([
                        'GET' => 'GET',
                        'POST' => 'POST',
                    ]),

                SelectFilter::make('table_name')
                    ->label('Table')
                    ->options([
                        'ATTLOG' => 'ATTLOG',
                        'OPERLOG' => 'OPERLOG',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
