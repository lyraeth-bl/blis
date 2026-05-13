<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Widgets\AttendanceStatusLineChart;
use App\Models\Employee;

class EmployeeAttendanceStatusLineChart extends AttendanceStatusLineChart
{
    protected ?string $heading = 'Absensi Karyawan';

    protected function getAttendableType(): string
    {
        return Employee::class;
    }
}
