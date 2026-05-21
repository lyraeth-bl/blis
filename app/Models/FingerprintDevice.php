<?php

namespace App\Models;

use App\Services\FingerprintClient;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

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

    public function getClient(): FingerprintClient
    {
        return new FingerprintClient(
            target: $this->ip_address,
            port: $this->port,
            commKey: $this->comm_key,
        );
    }
}
