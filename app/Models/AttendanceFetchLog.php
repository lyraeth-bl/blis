<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'sync_id',
    'fingerprint_device_id',
    'user_id',
    'device_name',
    'device_type',
    'device_ip_address',
    'device_port',
    'status',
    'fetched',
    'inserted',
    'updated',
    'skipped',
    'failed',
    'first_log_at',
    'last_log_at',
    'elapsed_ms',
    'raw_rows_sample',
    'error_message',
    'started_at',
    'finished_at',
])]
class AttendanceFetchLog extends Model
{
    protected function casts(): array
    {
        return [
            'raw_rows_sample' => 'array',
            'device_port' => 'integer',
            'fetched' => 'integer',
            'inserted' => 'integer',
            'updated' => 'integer',
            'skipped' => 'integer',
            'failed' => 'integer',
            'elapsed_ms' => 'integer',
            'first_log_at' => 'datetime',
            'last_log_at' => 'datetime',
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
