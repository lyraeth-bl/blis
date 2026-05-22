<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nip')
                            ->label('NIP')
                            ->copyable(),
                        TextEntry::make('name')
                            ->label('Nama')
                            ->weight('bold'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->placeholder('-')
                            ->copyable(),
                        TextEntry::make('position')
                            ->label('Jabatan')
                            ->placeholder('-'),
                        TextEntry::make('unitModel.display_name')
                            ->label('Unit')
                            ->badge()
                            ->placeholder('-'),
                    ]),

                Section::make('Catatan')
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
