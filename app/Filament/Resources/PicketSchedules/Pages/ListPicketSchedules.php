<?php

namespace App\Filament\Resources\PicketSchedules\Pages;

use App\Filament\Resources\PicketSchedules\PicketScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPicketSchedules extends ListRecords
{
    protected static string $resource = PicketScheduleResource::class;

    public function getSubheading(): string
    {
        return 'Kelola jadwal guru piket per unit untuk akses QR scanner absensi siswa.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
