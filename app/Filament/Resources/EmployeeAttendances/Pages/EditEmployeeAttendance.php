<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

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
}
