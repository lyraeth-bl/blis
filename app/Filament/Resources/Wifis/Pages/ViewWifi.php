<?php

namespace App\Filament\Resources\Wifis\Pages;

use App\Filament\Resources\Wifis\WifiResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWifi extends ViewRecord
{
    protected static string $resource = WifiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
