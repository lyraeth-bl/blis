<?php

namespace App\Filament\Resources\FingerprintDevices\Pages;

use App\Filament\Resources\FingerprintDevices\FingerprintDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFingerprintDevices extends ListRecords
{
    protected static string $resource = FingerprintDeviceResource::class;

    public function getSubheading(): string
    {
        return sprintf(
            'Device ADMS tidak perlu ditambahkan manual. Data mesin akan terisi otomatis setelah URL aplikasi %s dimasukkan ke pengaturan ADMS mesin.',
            'budiluhur.web.id',
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
