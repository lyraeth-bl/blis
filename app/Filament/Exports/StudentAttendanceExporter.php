<?php

namespace App\Filament\Exports;

use App\Models\Attendance;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class StudentAttendanceExporter extends Exporter
{
    protected static ?string $model = Attendance::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('attendable.nis')
                ->label('NIS'),

            ExportColumn::make('attendable.name')
                ->label('Nama'),

            ExportColumn::make('attendable.unit')
                ->label('Unit'),

            ExportColumn::make('attendable.class')
                ->label('Kelas'),

            ExportColumn::make('date')
                ->label('Tanggal'),

            ExportColumn::make('check_in')
                ->label('Jam Masuk'),

            ExportColumn::make('check_out')
                ->label('Jam Keluar'),

            ExportColumn::make('status')
                ->label('Status'),

            ExportColumn::make('source')
                ->label('Sumber'),

            ExportColumn::make('reason')
                ->label('Alasan'),

            ExportColumn::make('description')
                ->label('Deskripsi'),

            ExportColumn::make('fingerprintDevice.name')
                ->label('Device'),

            ExportColumn::make('edited_by')
                ->label('Diedit Oleh'),

            ExportColumn::make('edited_at')
                ->label('Diedit Pada'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Export absensi siswa selesai. '.Number::format($export->successful_rows).' '.str('baris')->plural($export->successful_rows).' berhasil diexport.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' baris gagal.';
        }

        return $body;
    }
}
