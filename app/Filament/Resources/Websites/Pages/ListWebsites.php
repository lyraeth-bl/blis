<?php

namespace App\Filament\Resources\Websites\Pages;

use App\Filament\Resources\Websites\WebsiteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListWebsites extends ListRecords
{
    protected static string $resource = WebsiteResource::class;

    public function getSubheading(): string
    {
        return 'Simpan daftar website, akses login, kategori, dan catatan penting agar mudah ditemukan kembali.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->icon(Heroicon::PlusCircle)->label('Tambah website'),
        ];
    }
}
