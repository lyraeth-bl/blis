<?php

namespace App\Filament\Widgets;

use App\Filament\Support\Widgets\AttendanceStatusLineChart;
use App\Models\Student;

class StudentAttendanceStatusLineChart extends AttendanceStatusLineChart
{
    protected ?string $heading = 'Absensi Siswa dan Siswi';

    protected function getAttendableType(): string
    {
        return Student::class;
    }
}
