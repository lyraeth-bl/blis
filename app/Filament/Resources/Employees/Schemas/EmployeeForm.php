<?php

namespace App\Filament\Resources\Employees\Schemas;

use App\Models\Unit;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Karyawan')->schema([
                    TextInput::make('nip')
                        ->label('NIP')
                        ->required()
                        ->unique(ignoreRecord: true),

                    TextInput::make('name')
                        ->label('Nama')
                        ->required(),

                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->endsWith('@budiluhur.sch.id')
                        ->hint('Optional')
                        ->unique(ignoreRecord: true),

                    TextInput::make('position')
                        ->label('Jabatan')
                        ->hint('Optional'),

                    Select::make('unit_id')
                        ->label('Unit')
                        ->options(fn (): array => Unit::query()
                            ->when(
                                ! Auth::user()?->isAdmin(),
                                fn ($query) => $query->whereIn('id', Auth::user()?->accessibleUnitIds() ?? []),
                            )
                            ->orderBy('campus')
                            ->orderBy('name')
                            ->get()
                            ->mapWithKeys(fn (Unit $unit): array => [$unit->id => $unit->display_name])
                            ->all())
                        ->native(false)
                        ->searchable()
                        ->required(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
