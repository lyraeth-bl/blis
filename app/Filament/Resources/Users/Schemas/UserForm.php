<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Akun')->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->helperText(fn (?User $record): string => $record === null
                            ? 'Password awal untuk user baru.'
                            : 'Kosongkan kalau tidak ingin reset password. Password lama tidak bisa dilihat karena disimpan sebagai hash.')
                        ->required(fn (?User $record): bool => $record === null)
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(255),

                    Select::make('role')
                        ->label('Role')
                        ->options(collect(UserRole::cases())
                            ->mapWithKeys(fn (UserRole $role): array => [$role->value => $role->label()])
                            ->all())
                        ->native(false)
                        ->required(),

                    Toggle::make('is_active')
                        ->label('Akun aktif')
                        ->helperText('User nonaktif tidak bisa login ke panel.')
                        ->default(true)
                        ->disabled(fn (?User $record): bool => $record?->is(Auth::user()) ?? false)
                        ->dehydrated(fn (?User $record): bool => ! ($record?->is(Auth::user()) ?? false)),

                    Select::make('units')
                        ->label('Unit')
                        ->relationship('units', 'code')
                        ->getOptionLabelFromRecordUsing(fn ($record): string => $record->display_name)
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->native(false)
                        ->helperText('Kosongkan untuk Admin. HRD/TU wajib diberi unit yang boleh diakses.'),
                ])->columns(2),
            ]);
    }
}
