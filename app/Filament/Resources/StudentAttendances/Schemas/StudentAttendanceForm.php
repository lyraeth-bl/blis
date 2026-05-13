<?php

namespace App\Filament\Resources\StudentAttendances\Schemas;

use App\Models\Student;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentAttendanceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Siswa')->schema([
                    Select::make('attendable_id')
                        ->label('Siswa')
                        ->options(Student::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required()
                        ->native(false)
                        ->reactive(),

                    Hidden::make('attendable_type')
                        ->default(Student::class),

                    Select::make('unit')
                        ->label('Unit')
                        ->native(false)
                        ->options([
                            'SMAKT' => 'SMAKT',
                            'SMKKT' => 'SMKKT',
                        ])
                        ->required(),
                ])->columns(2),

                Section::make('Data Absensi')->schema([
                    DatePicker::make('date')
                        ->label('Tanggal')
                        ->native(false)
                        ->required(),

                    Select::make('status')
                        ->label('Status')
                        ->native(false)
                        ->options([
                            'present' => 'Hadir',
                            'absent' => 'Tidak Hadir',
                            'late' => 'Terlambat',
                            'permitted' => 'Izin',
                        ])
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

                    Select::make('fingerprint_device_id')
                        ->label('Device')
                        ->relationship('fingerprintDevice', 'name')
                        ->searchable()
                        ->native(false)
                        ->nullable(),

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
