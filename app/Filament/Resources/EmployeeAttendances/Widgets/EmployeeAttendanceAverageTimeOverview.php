<?php

namespace App\Filament\Resources\EmployeeAttendances\Widgets;

use App\Filament\Support\Widgets\AverageAttendanceTimeOverview;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;

class EmployeeAttendanceAverageTimeOverview extends AverageAttendanceTimeOverview
{
    public static function canView(): bool
    {
        return Auth::user()?->canManageEmployees() ?? false;
    }

    protected function getAttendableType(): string
    {
        return Employee::class;
    }

    protected function getAttendanceSubjectLabel(): string
    {
        return 'karyawan';
    }
}
