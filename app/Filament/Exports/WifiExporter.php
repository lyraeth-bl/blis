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
            ExportColumn::make('ssid')
                ->label('SSID'),
            ExportColumn::make('location')
                ->label('Lokasi'),
            ExportColumn::make('ip_address')
                ->label('IP Address'),
            ExportColumn::make('password')
                ->label('Password'),
            ExportColumn::make('router_type')
                ->label('Tipe Router'),
            ExportColumn::make('admin_username')
                ->label('Username Admin'),
            ExportColumn::make('admin_password')
                ->label('Password Admin'),
            ExportColumn::make('link')
                ->label('Link Admin'),
            ExportColumn::make('units')
                ->label('Unit')
                ->state(fn (Wifi $record): string => $record->units->pluck('display_name')->join(', ') ?: 'Semua unit'),
            ExportColumn::make('is_private')
                ->label('Private'),
            ExportColumn::make('description')
                ->label('Deskripsi'),
            ExportColumn::make('created_at')
                ->label('Dibuat'),
            ExportColumn::make('updated_at')
                ->label('Diperbarui'),
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
