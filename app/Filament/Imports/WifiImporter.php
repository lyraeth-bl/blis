<?php

namespace App\Filament\Imports;

use App\Models\Wifi;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class WifiImporter extends Importer
{
    protected static ?string $model = Wifi::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('ssid')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('location')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('ip_address')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('password')
                ->rules(['max:255']),
            ImportColumn::make('router_type')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('admin_username')
                ->rules(['max:255']),
            ImportColumn::make('admin_password')
                ->rules(['max:255']),
            ImportColumn::make('link')
                ->rules(['max:255']),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): Wifi
    {
        return Wifi::firstOrNew([
            'ssid' => $this->data['ssid'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your wifi import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
