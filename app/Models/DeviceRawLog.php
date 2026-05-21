<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'fingerprint_device_id',
    'device_serial_number',
    'method',
    'endpoint',
    'query_payload',
    'body_payload',
    'table_name',
    'processed_count',
])]
class DeviceRawLog extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'query_payload' => 'array',
            'processed_count' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }
}
