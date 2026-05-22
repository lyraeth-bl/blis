<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use App\Models\Employee;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditEmployeeAttendance extends EditRecord
{
    protected static string $resource = EmployeeAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $employee = Employee::query()->find($data['attendable_id'] ?? null);

        abort_unless($employee !== null && Auth::user()?->canAccessUnit($employee->unit_id), 403);

        return $data;
    }
}
