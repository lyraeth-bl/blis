<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use App\Models\Unit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class EmployeeImporter extends Importer
{
    protected static ?string $model = Employee::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nip')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('email')
                ->rules(['email', 'max:255']),
            ImportColumn::make('position')
                ->rules(['max:255']),
            ImportColumn::make('unit')
                ->requiredMapping()
                ->rules(['required', 'exists:units,code'])
                ->fillRecordUsing(function (Employee $record, ?string $state): void {
                    $record->unit_id = filled($state)
                        ? Unit::query()->where('code', $state)->value('id')
                        : null;
                }),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): Employee
    {
        return Employee::firstOrNew([
            'nip' => $this->data['nip'],
        ]);
    }

    protected function beforeSave(): void
    {
        abort_unless(Auth::user()?->canManageEmployees(), 403);
        abort_unless(Auth::user()?->canAccessUnit($this->record->unit_id), 403);

        if ($this->record->exists && ! Auth::user()?->canAccessUnit($this->record->getOriginal('unit_id'))) {
            throw ValidationException::withMessages([
                'nip' => 'Data karyawan ini berada di luar unit yang boleh kamu akses.',
            ]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your employee import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
