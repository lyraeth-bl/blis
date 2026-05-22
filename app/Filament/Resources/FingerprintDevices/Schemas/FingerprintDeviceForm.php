<?php

namespace App\Filament\Resources\FingerprintDevices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FingerprintDeviceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Device')->schema([
                    TextInput::make('name')
                        ->label('Nama Device')
                        ->required(),

                    TextInput::make('location')
                        ->label('Lokasi'),

                    TextInput::make('serial_number')
                        ->label('Serial Number ADMS')
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Select::make('type')
                        ->label('Tipe')
                        ->options([
                            'student' => 'Siswa',
                            'employee' => 'Karyawan',
                        ])
                        ->native(false)
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set) {
                            if ($state === 'student') {
                                $set('check_in_start', '05:00');
                                $set('check_in_end', '07:00');
                                $set('check_out_start', '15:00');
                            } elseif ($state === 'employee') {
                                $set('check_in_start', '05:00');
                                $set('check_in_end', '07:30');
                                $set('check_out_start', '15:30');
                            }
                        }),

                    Select::make('units')
                        ->label('Unit')
                        ->relationship('units', 'code')
                        ->getOptionLabelFromRecordUsing(fn ($record): string => $record->display_name)
                        ->multiple()
                        ->preload()
                        ->native(false)
                        ->searchable()
                        ->required(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('Koneksi')->schema([
                    TextInput::make('ip_address')
                        ->label('IP Address'),

                    TextInput::make('port')
                        ->label('Port')
                        ->numeric()
                        ->default(80)
                        ->disabledOn('create')
                        ->required(),

                    TextInput::make('comm_key')
                        ->label('Comm Key')
                        ->default('0'),
                ])->columns(3),

                Section::make('Pengaturan Jam')->schema([
                    TextInput::make('check_in_start')
                        ->label('Mulai Check-in')
                        ->type('time')
                        ->required()
                        ->dehydrated(true)
                        ->reactive(),

                    TextInput::make('check_in_end')
                        ->label('Batas Hadir (Terlambat)')
                        ->type('time')
                        ->required()
                        ->dehydrated(true)
                        ->reactive(),

                    TextInput::make('check_out_start')
                        ->label('Mulai Check-out')
                        ->type('time')
                        ->required()
                        ->dehydrated(true)
                        ->reactive(),
                ])->columns(3),
            ]);
    }
}
