<?php

namespace App\Filament\Resources\Wifis\Pages;

use App\Filament\Resources\Wifis\WifiResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditWifi extends EditRecord
{
    protected static string $resource = WifiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
