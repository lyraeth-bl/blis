<?php

namespace App\Filament\Imports;

use App\Models\Attendance;
use App\Models\Employee;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class EmployeeAttendanceImporter extends Importer
{
    protected static ?string $model = Attendance::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nip')
                ->label('NIP')
                ->requiredMapping()
                ->rules(['required', 'string']),

            ImportColumn::make('date')
                ->label('Tanggal')
                ->requiredMapping()
                ->rules(['required', 'date']),

            ImportColumn::make('check_in')
                ->label('Jam Masuk')
                ->rules(['nullable', 'date_format:H:i']),

            ImportColumn::make('check_out')
                ->label('Jam Keluar')
                ->rules(['nullable', 'date_format:H:i']),

            ImportColumn::make('status')
                ->label('Status')
                ->requiredMapping()
                ->rules(['required', 'in:present,absent,late,permitted']),

            ImportColumn::make('reason')
                ->label('Alasan')
                ->rules(['nullable', 'string']),

            ImportColumn::make('description')
                ->label('Deskripsi')
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): Attendance
    {
        $employee = Employee::where('nip', $this->data['nip'])->first();

        return Attendance::firstOrNew([
            'attendable_type' => Employee::class,
            'attendable_id' => $employee?->id,
            'date' => $this->data['date'],
        ]);
    }

    protected function beforeSave(): void
    {
        $employee = Employee::where('nip', $this->data['nip'])->first();

        $this->record->attendable_type = Employee::class;
        $this->record->attendable_id = $employee?->id;
        $this->record->source = 'manual';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import absensi karyawan selesai. '.Number::format($import->successful_rows).' '.str('baris')->plural($import->successful_rows).' berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal.';
        }

        return $body;
    }
}
