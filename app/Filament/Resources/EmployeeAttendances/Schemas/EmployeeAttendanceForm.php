<?php

namespace App\Filament\Resources\EmployeeAttendances\Schemas;

use App\Models\Employee;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EmployeeAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Karyawan')->schema([
                    Select::make('attendable_id')
                        ->label('Karyawan')
                        ->options(Employee::query()->pluck('name', 'id'))
                        ->searchable()
                        ->native(false)
                        ->required(),

                    Hidden::make('attendable_type')
                        ->default(Employee::class),

                    Select::make('fingerprint_device_id')
                        ->label('Device')
                        ->relationship('fingerprintDevice', 'name')
                        ->searchable()
                        ->native(false)
                        ->nullable(),
                ])->columns(2),

                Section::make('Data Absensi')->schema([
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->native(false)
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'present' => 'Hadir',
                            'absent' => 'Tidak Hadir',
                            'late' => 'Terlambat',
                            'permitted' => 'Izin',
                        ])
                        ->native(false)
                        ->required(),

                    TimePicker::make('check_in')
                        ->label('Jam Masuk')
                        ->native(false)
                        ->seconds(false),

                    TimePicker::make('check_out')
                        ->label('Jam Keluar')
                        ->native(false)
                        ->seconds(false),

                    Select::make('source')
                        ->label('Sumber')
                        ->native(false)
                        ->options([
                            'manual' => 'Manual',
                            'fingerprint' => 'Fingerprint',
                        ])
                        ->default('manual')
                        ->required(),

                    Textarea::make('reason')
                        ->label('Alasan')
                        ->rows(2)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),
            ]);
    }
}
