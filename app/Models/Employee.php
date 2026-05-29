<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Collection;

#[Fillable([
    'nip',
    'name',
    'email',
    'position',
    'unit_id',
    'description',
])]
class Employee extends Model
{
    public function unitModel(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'attendable_id')
            ->where('attendable_type', self::class);
    }

    public function picketSchedules(): HasMany
    {
        return $this->hasMany(PicketSchedule::class);
    }

    public function fingerprintDevices(): MorphToMany
    {
        return $this->morphToMany(FingerprintDevice::class, 'attendable', 'fingerprint_device_users')
            ->withPivot('pushed_at')
            ->withTimestamps();
    }

    public function getPinAttribute(): string
    {
        return $this->nip;
    }

    public function accessibleUnitsLabel(): string
    {
        $units = $this->relationLoaded('units')
            ? $this->units
            : $this->units()->orderBy('name')->orderBy('campus')->get();

        if ($this->unitModel !== null && ! $units->contains('id', $this->unitModel->id)) {
            $units->push($this->unitModel);
        }

        return $units
            ->sortBy([['name', 'asc'], ['campus', 'asc']])
            ->pluck('display_name')
            ->join(', ') ?: '-';
    }

    /**
     * @return Collection<int, int>
     */
    public function accessibleUnitIds(): Collection
    {
        $unitIds = $this->units()->pluck('units.id');

        if ($this->unit_id !== null) {
            $unitIds->push($this->unit_id);
        }

        return $unitIds->unique()->values();
    }
}
