<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

#[Fillable([
    'nis',
    'name',
    'unit',
    'class',
    'description',
])]
class Student extends Model
{
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'attendable_id')
            ->where('attendable_type', self::class);
    }

    public function fingerprintDevices(): MorphToMany
    {
        return $this->morphToMany(FingerprintDevice::class, 'attendable', 'fingerprint_device_users')
            ->withPivot('pushed_at')
            ->withTimestamps();
    }

    public function getPinAttribute(): string
    {
        return $this->nis;
    }
}
