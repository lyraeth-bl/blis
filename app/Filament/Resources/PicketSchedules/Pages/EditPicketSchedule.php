<?php

namespace App\Filament\Resources\PicketSchedules\Pages;

use App\Filament\Resources\PicketSchedules\PicketScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPicketSchedule extends EditRecord
{
    protected static string $resource = PicketScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
