<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Widgets\AttendanceStatusLineChart;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentAttendanceStatusLineChart extends AttendanceStatusLineChart
{
    protected ?string $heading = 'Absensi Siswa dan Siswi';

    public static function canView(): bool
    {
        return Auth::user()?->canManageStudents() ?? false;
    }

    protected function getAttendableType(): string
    {
        return Student::class;
    }
}
