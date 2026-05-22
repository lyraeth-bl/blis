<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Profil Login')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama')
                            ->weight('bold'),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->badge()
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('password_status')
                            ->label('Password')
                            ->state('Tersimpan aman sebagai hash. Gunakan edit untuk reset password.'),
                    ])
                    ->columns(2),

                Section::make('Hak Akses')
                    ->schema([
                        TextEntry::make('role')
                            ->label('Role')
                            ->formatStateUsing(fn (UserRole|string $state): string => $state instanceof UserRole ? $state->label() : UserRole::from($state)->label())
                            ->badge()
                            ->color(fn (UserRole|string $state): string => match ($state instanceof UserRole ? $state : UserRole::from($state)) {
                                UserRole::Admin => 'danger',
                                UserRole::Hrd => 'warning',
                                UserRole::Tu => 'info',
                            }),
                        TextEntry::make('units.display_name')
                            ->label('Unit')
                            ->badge()
                            ->separator(', ')
                            ->placeholder('Semua unit'),
                    ])
                    ->columns(2),

                Section::make('Audit')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Diubah')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
