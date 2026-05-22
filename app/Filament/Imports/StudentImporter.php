<?php

namespace App\Filament\Imports;

use App\Models\Student;
use App\Models\Unit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Illuminate\Validation\ValidationException;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('name')
                ->requiredMapping()
                ->rules(['required', 'max:255']),
            ImportColumn::make('unit')
                ->requiredMapping()
                ->rules(['required', 'exists:units,code'])
                ->fillRecordUsing(function (Student $record, string $state): void {
                    $record->unit = $state;
                    $record->unit_id = Unit::query()
                        ->where('code', $state)
                        ->value('id');
                }),
            ImportColumn::make('class')
                ->rules(['max:255']),
            ImportColumn::make('description'),
        ];
    }

    public function resolveRecord(): Student
    {
        return Student::firstOrNew([
            'nis' => $this->data['nis'],
        ]);
    }

    protected function beforeSave(): void
    {
        abort_unless(Auth::user()?->canManageStudents(), 403);
        abort_unless(Auth::user()?->canAccessUnit($this->record->unit_id), 403);

        if ($this->record->exists && ! Auth::user()?->canAccessUnit($this->record->getOriginal('unit_id'))) {
            throw ValidationException::withMessages([
                'nis' => 'Data siswa ini berada di luar unit yang boleh kamu akses.',
            ]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your student import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
