<?php

namespace App\Filament\Exports;

use App\Models\Wifi;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class WifiExporter extends Exporter
{
    protected static ?string $model = Wifi::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('ssid')->label('SSID'),
            ExportColumn::make('location')->label('Location'),
            ExportColumn::make('password')->label('Password'),
            ExportColumn::make('ip_address')->label('IP address'),
            ExportColumn::make('router_type')->label('Router type'),
            ExportColumn::make('admin_username')->label('Admin username'),
            ExportColumn::make('admin_password')->label('Admin password'),
            ExportColumn::make('link')->label('Link'),
            ExportColumn::make('description')->label('Deskripsi'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your wifi export has completed and '.Number::format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
