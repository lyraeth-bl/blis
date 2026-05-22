<?php

namespace App\Filament\Imports;

use App\Models\Employee;
use App\Models\PicketSchedule;
use App\Models\Unit;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PicketScheduleImporter extends Importer
{
    protected static ?string $model = PicketSchedule::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('employee_email')
                ->label('Email Guru / Staff')
                ->requiredMapping()
                ->rules(['required', 'email', 'exists:employees,email'])
                ->fillRecordUsing(function (PicketSchedule $record, string $state): void {
                    $record->employee_id = Employee::query()
                        ->where('email', Str::lower($state))
                        ->value('id');
                }),
            ImportColumn::make('unit')
                ->label('Kode Unit')
                ->requiredMapping()
                ->rules(['required', 'exists:units,code'])
                ->fillRecordUsing(function (PicketSchedule $record, string $state): void {
                    $record->unit_id = Unit::query()
                        ->where('code', $state)
                        ->value('id');
                }),
            ImportColumn::make('day')
                ->label('Hari')
                ->requiredMapping()
                ->rules(['required'])
                ->fillRecordUsing(function (PicketSchedule $record, string|int $state): void {
                    $record->day_of_week = self::dayToNumber($state);
                }),
            ImportColumn::make('starts_at')
                ->label('Mulai')
                ->requiredMapping()
                ->rules(['required', 'date_format:H:i']),
            ImportColumn::make('ends_at')
                ->label('Selesai')
                ->requiredMapping()
                ->rules(['required', 'date_format:H:i']),
            ImportColumn::make('effective_from')
                ->label('Berlaku Dari')
                ->requiredMapping()
                ->rules(['required', 'date']),
            ImportColumn::make('effective_until')
                ->label('Berlaku Sampai')
                ->requiredMapping()
                ->rules(['required', 'date', 'after_or_equal:effective_from']),
            ImportColumn::make('is_active')
                ->label('Aktif')
                ->rules(['boolean']),
        ];
    }

    public function resolveRecord(): PicketSchedule
    {
        $employeeId = Employee::query()
            ->where('email', Str::lower((string) $this->data['employee_email']))
            ->value('id');

        $unitId = Unit::query()
            ->where('code', $this->data['unit'])
            ->value('id');

        return PicketSchedule::firstOrNew([
            'employee_id' => $employeeId,
            'unit_id' => $unitId,
            'day_of_week' => self::dayToNumber($this->data['day']),
            'starts_at' => $this->data['starts_at'],
            'effective_from' => $this->data['effective_from'],
            'effective_until' => $this->data['effective_until'],
        ]);
    }

    protected function beforeSave(): void
    {
        abort_unless(Auth::user()?->canManagePicketSchedules(), 403);
        abort_unless(Auth::user()?->canAccessUnit($this->record->unit_id), 403);

        if ($this->record->exists && ! Auth::user()?->canAccessUnit($this->record->getOriginal('unit_id'))) {
            throw ValidationException::withMessages([
                'unit' => 'Jadwal piket ini berada di luar unit yang boleh kamu akses.',
            ]);
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your picket schedule import has completed and '.Number::format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.Number::format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }

    private static function dayToNumber(string|int $day): int
    {
        if (is_numeric($day)) {
            $number = (int) $day;

            if ($number >= 1 && $number <= 7) {
                return $number;
            }
        }

        return match (Str::lower(trim((string) $day))) {
            'senin', 'monday' => 1,
            'selasa', 'tuesday' => 2,
            'rabu', 'wednesday' => 3,
            'kamis', 'thursday' => 4,
            'jumat', 'jum\'at', 'friday' => 5,
            'sabtu', 'saturday' => 6,
            'minggu', 'ahad', 'sunday' => 7,
            default => throw ValidationException::withMessages([
                'day' => 'Hari harus berupa angka 1-7 atau nama hari yang valid.',
            ]),
        };
    }
}
