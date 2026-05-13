<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeAttendance extends ViewRecord
{
    protected static string $resource = EmployeeAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
