<?php

namespace App\Filament\Resources\EmployeeAttendances\Widgets;

use App\Filament\Support\Widgets\AverageAttendanceTimeOverview;
use App\Models\Employee;

class EmployeeAttendanceAverageTimeOverview extends AverageAttendanceTimeOverview
{
    protected function getAttendableType(): string
    {
        return Employee::class;
    }

    protected function getAttendanceSubjectLabel(): string
    {
        return 'karyawan';
    }
}
