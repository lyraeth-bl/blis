<?php

namespace App\Filament\Resources\StudentAttendances\Schemas;

use App\Models\Attendance;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StudentAttendanceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Siswa')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('attendable.nis')
                            ->label('NIS')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('attendable.name')
                            ->label('Nama')
                            ->weight('bold')
                            ->placeholder('-'),
                        TextEntry::make('unit')
                            ->label('Unit')
                            ->badge()
                            ->state(fn (Attendance $record): string => $record->attendable?->unitModel?->display_name ?? $record->attendable?->unit ?? '-'),
                        TextEntry::make('attendable.class')
                            ->label('Kelas')
                            ->placeholder('-'),
                    ]),

                Section::make('Data Absensi')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('date')
                            ->label('Tanggal')
                            ->date(),
                        TextEntry::make('check_in')
                            ->label('Jam Masuk')
                            ->time()
                            ->placeholder('-'),
                        TextEntry::make('check_out')
                            ->label('Jam Keluar')
                            ->time()
                            ->placeholder('-'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'present' => 'Hadir',
                                'absent' => 'Tidak Hadir',
                                'late' => 'Terlambat',
                                'permitted' => 'Izin',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'present' => 'success',
                                'late' => 'warning',
                                'permitted' => 'info',
                                'absent' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('source')
                            ->label('Sumber')
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => match ($state) {
                                'manual' => 'Manual',
                                'fingerprint' => 'Fingerprint',
                                default => $state,
                            })
                            ->color(fn (string $state): string => match ($state) {
                                'fingerprint' => 'success',
                                'manual' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('fingerprintDevice.name')
                            ->label('Device')
                            ->placeholder('-'),
                        TextEntry::make('reason')
                            ->label('Alasan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Data ADMS')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('adms_pin')
                            ->label('PIN ADMS')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('adms_punch_time')
                            ->label('Waktu Punch')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('adms_status1')
                            ->label('Status 1')
                            ->placeholder('-'),
                        TextEntry::make('adms_status2')
                            ->label('Status 2')
                            ->placeholder('-'),
                        TextEntry::make('adms_status3')
                            ->label('Status 3')
                            ->placeholder('-'),
                        TextEntry::make('adms_status4')
                            ->label('Status 4')
                            ->placeholder('-'),
                        TextEntry::make('adms_status5')
                            ->label('Status 5')
                            ->placeholder('-'),
                        CodeEntry::make('adms_raw_payload')
                            ->label('Raw Payload')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),

                Section::make('Audit')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('edited_by')
                            ->label('Diedit oleh')
                            ->placeholder('-'),
                        TextEntry::make('edited_at')
                            ->label('Diedit pada')
                            ->dateTime()
                            ->placeholder('-'),
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
