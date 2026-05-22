<?php

namespace App\Filament\Resources\StudentAttendances\Pages;

use App\Filament\Resources\StudentAttendances\StudentAttendanceResource;
use App\Models\Student;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateStudentAttendance extends CreateRecord
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $student = Student::query()->find($data['attendable_id'] ?? null);

        abort_unless($student !== null && Auth::user()?->canAccessUnit($student->unit_id), 403);

        return $data;
    }
}
