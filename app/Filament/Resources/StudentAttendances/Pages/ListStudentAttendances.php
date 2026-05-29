<?php

namespace App\Filament\Resources\StudentAttendances\Pages;

use App\Filament\Resources\StudentAttendances\StudentAttendanceResource;
use App\Filament\Resources\StudentAttendances\Widgets\StudentAttendanceAverageTimeOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStudentAttendances extends ListRecords
{
    protected static string $resource = StudentAttendanceResource::class;

    public function getSubheading(): string
    {
        return 'Kelola data absensi siswa dari scan QR, input manual, dan mesin fingerprint dalam satu daftar.';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('scan_qr')
                ->label('Scan QR Absensi')
                ->icon('heroicon-o-qr-code')
                ->url(route('qr-attendance.index'))
                ->color('gray'),
            CreateAction::make()->label('Absensi Manual'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StudentAttendanceAverageTimeOverview::class,
        ];
    }
}
