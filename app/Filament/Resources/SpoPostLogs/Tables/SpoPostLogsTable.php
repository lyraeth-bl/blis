<?php

namespace App\Filament\Resources\SpoPostLogs\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpoPostLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('attempted_at')
                    ->label('Waktu')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'gray',
                        default => 'warning',
                    })
                    ->sortable(),

                TextColumn::make('endpoint_type')
                    ->label('Endpoint')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'attendance' => 'Absensi',
                        'notification' => 'Notifikasi',
                        default => $state,
                    }),

                TextColumn::make('field')
                    ->label('Aksi')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('fingerprintDevice.name')
                    ->label('Mesin')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('attendable.name')
                    ->label('Nama')
                    ->placeholder('-'),

                TextColumn::make('http_status')
                    ->label('HTTP')
                    ->placeholder('-')
                    ->sortable(),

                TextColumn::make('skipped_reason')
                    ->label('Skip/Error')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(),
            ])
            ->defaultSort('attempted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),

                SelectFilter::make('endpoint_type')
                    ->label('Endpoint')
                    ->options([
                        'attendance' => 'Absensi',
                        'notification' => 'Notifikasi',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
