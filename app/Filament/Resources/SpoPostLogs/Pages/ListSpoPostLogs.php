<?php

namespace App\Filament\Resources\SpoPostLogs\Pages;

use App\Filament\Resources\SpoPostLogs\SpoPostLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSpoPostLogs extends ListRecords
{
    protected static string $resource = SpoPostLogResource::class;

    public function getSubheading(): string
    {
        return 'Pantau hasil pengiriman data absensi ke endpoint SPO, termasuk payload, response, status HTTP, dan error.';
    }
}
