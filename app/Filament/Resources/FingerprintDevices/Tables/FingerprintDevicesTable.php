<?php

namespace App\Filament\Resources\FingerprintDevices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FingerprintDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),

                TextColumn::make('ip_address')
                    ->label('IP Address'),

                TextColumn::make('port')
                    ->label('Port'),

                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'student' => 'info',
                        'employee' => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateIcon(Heroicon::OutlinedFingerPrint)
            ->emptyStateHeading('Belum ada mesin nih')
            ->emptyStateDescription('Daftarin mesin fingerprint dulu, nanti datanya nongol otomatis di sini.');
    }
}
