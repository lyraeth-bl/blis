<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Widgets\AttendanceStatusLineChart;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceStatusLineChart extends AttendanceStatusLineChart
{
    protected ?string $heading = 'Absensi Karyawan';

    public static function canView(): bool
    {
        return Auth::user()?->canManageEmployees() ?? false;
    }

    protected function getAttendableType(): string
    {
        return Employee::class;
    }
}
