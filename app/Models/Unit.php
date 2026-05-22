<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'code',
    'name',
    'campus',
    'description',
])]
class Unit extends Model
{
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function fingerprintDevices(): BelongsToMany
    {
        return $this->belongsToMany(FingerprintDevice::class)
            ->withTimestamps();
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} - {$this->campus}";
    }
}
