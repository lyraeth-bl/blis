<?php

namespace App\Filament\Resources\StudentAttendances\Pages;

use App\Filament\Resources\StudentAttendances\StudentAttendanceResource;
use App\Models\Student;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditStudentAttendance extends EditRecord
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $student = Student::query()->find($data['attendable_id'] ?? null);

        abort_unless($student !== null && Auth::user()?->canAccessUnit($student->unit_id), 403);

        return $data;
    }
}
