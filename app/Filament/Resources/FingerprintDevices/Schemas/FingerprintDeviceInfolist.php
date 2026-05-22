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
                        TextEntry::make('id')
                            ->label('ID')
                            ->badge(),
                        TextEntry::make('name')
                            ->label('Nama Device'),
                        TextEntry::make('units.display_name')
                            ->label('Unit')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('-'),
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

                Section::make('Informasi Device')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('serial_number')
                            ->label('Serial Number ADMS')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('type')
                            ->label('Tipe')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'student' => 'Siswa',
                                'employee' => 'Karyawan',
                                default => $state,
                            }),
                        TextEntry::make('location')
                            ->label('Lokasi')
                            ->placeholder('-'),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Koneksi')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label('IP Address')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('port')
                            ->label('Port')
                            ->numeric(),
                        TextEntry::make('comm_key')
                            ->label('Comm Key')
                            ->copyable(),
                    ]),

                Section::make('Pengaturan Jam')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('check_in_start')
                            ->label('Mulai Check-in')
                            ->time(),
                        TextEntry::make('check_in_end')
                            ->label('Batas Hadir')
                            ->time(),
                        TextEntry::make('check_out_start')
                            ->label('Mulai Check-out')
                            ->time(),
                    ]),

                Section::make('Timestamps')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
