<?php

namespace App\Filament\Resources\Wifis\Pages;

use App\Filament\Resources\Wifis\WifiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListWifis extends ListRecords
{
    protected static string $resource = WifiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::PlusCircle)->label('Tambah wifi'),
        ];
    }
}
