<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Siswa')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('nis')
                            ->label('NIS')
                            ->copyable(),
                        TextEntry::make('name')
                            ->label('Nama')
                            ->weight('bold'),
                        TextEntry::make('unitModel.display_name')
                            ->label('Unit')
                            ->badge(),
                        TextEntry::make('unit')
                            ->label('Kode Unit')
                            ->badge()
                            ->placeholder('-'),
                        TextEntry::make('class')
                            ->label('Kelas')
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
