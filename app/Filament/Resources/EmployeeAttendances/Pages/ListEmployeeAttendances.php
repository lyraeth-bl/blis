<?php

namespace App\Filament\Resources\EmployeeAttendances\Pages;

use App\Filament\Resources\EmployeeAttendances\EmployeeAttendanceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAttendances extends ListRecords
{
    protected static string $resource = EmployeeAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
