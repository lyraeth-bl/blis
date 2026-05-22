<?php

namespace App\Filament\Resources\PicketSchedules\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PicketScheduleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jadwal Piket')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('employee.name')
                            ->label('Guru / Staff'),
                        TextEntry::make('unitModel.display_name')
                            ->label('Unit'),
                        TextEntry::make('day_label')
                            ->label('Hari')
                            ->badge(),
                        TextEntry::make('starts_at')
                            ->label('Mulai')
                            ->time('H:i'),
                        TextEntry::make('ends_at')
                            ->label('Selesai')
                            ->time('H:i'),
                        TextEntry::make('effective_from')
                            ->label('Berlaku Dari')
                            ->date(),
                        TextEntry::make('effective_until')
                            ->label('Berlaku Sampai')
                            ->date(),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->badge(),
                    ]),
            ]);
    }
}
