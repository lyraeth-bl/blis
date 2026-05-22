<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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
}
