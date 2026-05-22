<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use App\Models\Employee;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateEmployeeAttendance extends CreateRecord
{
    protected static string $resource = EmployeeAttendanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = Employee::query()->find($data['attendable_id'] ?? null);

        abort_unless($employee !== null && Auth::user()?->canAccessUnit($employee->unit_id), 403);

        return $data;
    }
}
