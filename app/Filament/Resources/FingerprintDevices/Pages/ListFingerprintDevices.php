<?php

namespace App\Filament\Resources\FingerprintDevices\Pages;

use App\Filament\Resources\FingerprintDevices\FingerprintDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFingerprintDevices extends ListRecords
{
    protected static string $resource = FingerprintDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
