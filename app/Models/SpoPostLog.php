<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'attendance_fetch_log_id',
    'fingerprint_device_id',
    'attendable_type',
    'attendable_id',
    'endpoint_type',
    'field',
    'status',
    'url',
    'http_status',
    'payload',
    'response_body',
    'error_message',
    'skipped_reason',
    'attempted_at',
])]
class SpoPostLog extends Model
{
    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'http_status' => 'integer',
            'attempted_at' => 'datetime',
        ];
    }

    public function attendanceFetchLog(): BelongsTo
    {
        return $this->belongsTo(AttendanceFetchLog::class);
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }
}
