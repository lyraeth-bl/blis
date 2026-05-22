<?php

namespace App\Filament\Resources\StudentAttendances\Widgets;

use App\Filament\Support\Widgets\AverageAttendanceTimeOverview;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentAttendanceAverageTimeOverview extends AverageAttendanceTimeOverview
{
    public static function canView(): bool
    {
        return Auth::user()?->canManageStudents() ?? false;
    }

    protected function getAttendableType(): string
    {
        return Student::class;
    }

    protected function getAttendanceSubjectLabel(): string
    {
        return 'siswa dan siswi';
    }
}
