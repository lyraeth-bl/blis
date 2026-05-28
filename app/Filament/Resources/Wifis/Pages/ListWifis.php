<?php

namespace App\Filament\Resources\Wifis\Pages;

use App\Filament\Resources\Wifis\WifiResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWifis extends ListRecords
{
    protected static string $resource = WifiResource::class;

    public function getSubheading(): string
    {
        return 'Kelola informasi jaringan wifi, akses router, lokasi, dan kredensial admin yang dibutuhkan operasional.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Wifi'),
        ];
    }
}
