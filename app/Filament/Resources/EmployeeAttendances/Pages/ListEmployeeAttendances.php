<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use App\Filament\Resources\EmployeeAttendances\Widgets\EmployeeAttendanceAverageTimeOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAttendances extends ListRecords
{
    protected static string $resource = EmployeeAttendanceResource::class;

    public function getSubheading(): string
    {
        return 'Kelola data absensi karyawan dari input manual maupun mesin fingerprint, termasuk status hadir, terlambat, izin, dan tidak hadir.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            EmployeeAttendanceAverageTimeOverview::class,
        ];
    }
}
