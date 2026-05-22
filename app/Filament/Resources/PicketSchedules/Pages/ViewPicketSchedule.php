<?php

namespace App\Filament\Resources\PicketSchedules\Pages;

use App\Filament\Resources\PicketSchedules\PicketScheduleResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPicketSchedule extends ViewRecord
{
    protected static string $resource = PicketScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
