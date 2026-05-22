<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable([
    'employee_id',
    'unit_id',
    'day_of_week',
    'starts_at',
    'ends_at',
    'effective_from',
    'effective_until',
    'is_active',
])]
class PicketSchedule extends Model
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function unitModel(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function scopeActiveAt(Builder $query, ?Carbon $now = null): Builder
    {
        $now ??= now();

        return $query
            ->where('is_active', true)
            ->where('day_of_week', $now->dayOfWeekIso)
            ->whereDate('effective_from', '<=', $now->toDateString())
            ->whereDate('effective_until', '>=', $now->toDateString())
            ->whereTime('starts_at', '<=', $now->toTimeString())
            ->whereTime('ends_at', '>=', $now->toTimeString());
    }

    public function getDayLabelAttribute(): string
    {
        return match ($this->day_of_week) {
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
            default => '-',
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime:H:i',
            'ends_at' => 'datetime:H:i',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
