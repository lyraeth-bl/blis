<?php

namespace App\Filament\Resources\PicketSchedules\Schemas;

use App\Models\Employee;
use App\Models\Unit;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class PicketScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Jadwal Piket')
                    ->schema([
                        Select::make('employee_id')
                            ->label('Guru / Staff')
                            ->options(fn (): array => Employee::query()
                                ->when(
                                    ! Auth::user()?->isAdmin(),
                                    fn ($query) => $query->whereIn('unit_id', Auth::user()?->accessibleUnitIds() ?? []),
                                )
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Employee $employee): array => [$employee->id => "{$employee->name} ({$employee->email})"])
                                ->all())
                            ->native(false)
                            ->searchable()
                            ->required(),

                        Select::make('unit_id')
                            ->label('Unit Piket')
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

                        Select::make('day_of_week')
                            ->label('Hari')
                            ->options([
                                1 => 'Senin',
                                2 => 'Selasa',
                                3 => 'Rabu',
                                4 => 'Kamis',
                                5 => 'Jumat',
                                6 => 'Sabtu',
                                7 => 'Minggu',
                            ])
                            ->native(false)
                            ->required(),

                        TimePicker::make('starts_at')
                            ->label('Mulai')
                            ->seconds(false)
                            ->required(),

                        TimePicker::make('ends_at')
                            ->label('Selesai')
                            ->seconds(false)
                            ->required(),

                        DatePicker::make('effective_from')
                            ->label('Berlaku Dari')
                            ->required(),

                        DatePicker::make('effective_until')
                            ->label('Berlaku Sampai')
                            ->afterOrEqual('effective_from')
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
