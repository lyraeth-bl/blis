<?php

namespace App\Filament\Resources\DeviceRawLogs\Pages;

use App\Filament\Resources\DeviceRawLogs\DeviceRawLogResource;
use Filament\Resources\Pages\ListRecords;

class ListDeviceRawLogs extends ListRecords
{
    protected static string $resource = DeviceRawLogResource::class;

    public function getSubheading(): string
    {
        return 'Log mentah dari mesin fingerprint untuk memantau request ADMS, payload, dan jumlah data yang diproses.';
    }
}
