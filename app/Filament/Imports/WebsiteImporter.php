<?php

namespace App\Filament\Imports;

use App\Models\Website;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class WebsiteImporter extends Importer
{
    protected static ?string $model = Website::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->requiredMapping(false)
                ->rules(['required', 'max:255']),
            ImportColumn::make('url')
                ->requiredMapping(false)
                ->rules(['required', 'max:255']),
            ImportColumn::make('username')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('password')
                ->rules(['max:255']),
            ImportColumn::make('category')
                ->rules(['max:255']),
            ImportColumn::make('unit_id')
                ->rules(['nullable', 'integer', 'exists:units,id']),
            ImportColumn::make('is_private')
                ->rules(['boolean']),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): Website
    {
        return Website::firstOrNew([
            'url' => $this->data['url'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your website import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
