<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'attendable_type',
    'attendable_id',
    'fingerprint_device_id',
    'date',
    'check_in',
    'check_out',
    'status',
    'source',
    'reason',
    'description',
    'edited_by',
    'edited_at',
])]
class Attendance extends Model
{
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'check_in' => 'datetime:H:i',
            'check_out' => 'datetime:H:i',
            'edited_at' => 'datetime',
        ];
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }

    public function getNameAttribute(): string
    {
        return $this->attendable?->name ?? '-';
    }

    public function getPinAttribute(): string
    {
        return $this->attendable?->pin ?? '-';
    }
}
