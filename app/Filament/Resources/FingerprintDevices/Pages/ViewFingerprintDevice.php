<?php

namespace App\Filament\Resources\FingerprintDevices\Pages;

use App\Filament\Resources\FingerprintDevices\FingerprintDeviceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewFingerprintDevice extends ViewRecord
{
    protected static string $resource = FingerprintDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
