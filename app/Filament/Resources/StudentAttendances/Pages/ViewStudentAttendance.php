<?php

namespace App\Filament\Resources\StudentAttendances\Pages;

use App\Filament\Resources\StudentAttendances\StudentAttendanceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStudentAttendance extends ViewRecord
{
    protected static string $resource = StudentAttendanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
