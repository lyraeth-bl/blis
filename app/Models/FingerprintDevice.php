<?php

namespace App\Models;

use App\Services\FingerprintClient;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'serial_number',
    'name',
    'location',
    'ip_address',
    'port',
    'comm_key',
    'type',
    'last_seen_at',
    'check_in_start',
    'check_in_end',
    'check_out_start',
    'description',
])]
class FingerprintDevice extends Model
{
    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)
            ->withTimestamps();
    }

    public function supportsUnit(?int $unitId): bool
    {
        if ($unitId === null) {
            return false;
        }

        return $this->units()
            ->whereKey($unitId)
            ->exists();
    }

    public function getUnitDisplayNamesAttribute(): string
    {
        return $this->units
            ->pluck('display_name')
            ->join(', ');
    }

    public function getConnectionStatusAttribute(): string
    {
        if (! $this->last_seen_at instanceof Carbon) {
            return 'inactive';
        }

        return $this->last_seen_at->gte(now()->subSeconds(30))
            ? 'active'
            : 'inactive';
    }

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'check_in_start' => 'datetime:H:i',
            'check_in_end' => 'datetime:H:i',
            'check_out_start' => 'datetime:H:i',
            'last_seen_at' => 'datetime',
        ];
    }

    public function students(): MorphToMany
    {
        return $this->morphedByMany(Student::class, 'attendable', 'fingerprint_device_users')
            ->withPivot('pushed_at')
            ->withTimestamps();
    }

    public function employees(): MorphToMany
    {
        return $this->morphedByMany(Employee::class, 'attendable', 'fingerprint_device_users')
            ->withPivot('pushed_at')
            ->withTimestamps();
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function rawLogs(): HasMany
    {
        return $this->hasMany(DeviceRawLog::class);
    }

    public function commands(): HasMany
    {
        return $this->hasMany(FingerprintDeviceCommand::class);
    }

    public function getClient(): FingerprintClient
    {
        return new FingerprintClient(
            target: $this->ip_address,
            port: $this->port,
            commKey: $this->comm_key,
        );
    }
}
