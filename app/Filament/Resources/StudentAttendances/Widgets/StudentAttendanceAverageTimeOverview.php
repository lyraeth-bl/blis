<?php

namespace App\Filament\Resources\StudentAttendances\Widgets;

use App\Filament\Support\Widgets\AverageAttendanceTimeOverview;
use App\Models\Student;

class StudentAttendanceAverageTimeOverview extends AverageAttendanceTimeOverview
{
    protected function getAttendableType(): string
    {
        return Student::class;
    }

    protected function getAttendanceSubjectLabel(): string
    {
        return 'siswa dan siswi';
    }
}
