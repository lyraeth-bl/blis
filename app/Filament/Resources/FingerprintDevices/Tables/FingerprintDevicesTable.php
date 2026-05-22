<?php

namespace App\Filament\Resources\FingerprintDevices\Tables;

use App\Models\FingerprintDevice;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FingerprintDevicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('location')
                    ->label('Lokasi')
                    ->searchable(),

                TextColumn::make('units')
                    ->label('Unit')
                    ->state(fn (FingerprintDevice $record): string => $record->unit_display_names)
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('serial_number')
                    ->label('Serial ADMS')
                    ->searchable()
                    ->toggleable(),

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

                TextColumn::make('connection_status')
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

                TextColumn::make('last_seen_at')
                    ->label('Terakhir Online')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('units')
                    ->label('Unit')
                    ->relationship('units', 'code')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->display_name)
                    ->multiple()
                    ->searchable()
                    ->preload(),
                SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'student' => 'Siswa',
                        'employee' => 'Karyawan',
                    ]),
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
