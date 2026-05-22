<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'fingerprint_device_id',
    'attendable_type',
    'attendable_id',
    'requested_by_user_id',
    'command_id',
    'action',
    'command',
    'payload',
    'status',
    'return_code',
    'raw_reply',
    'reply_payload',
    'comparison_status',
    'comparison_details',
    'error_message',
    'sent_at',
    'replied_at',
    'expires_at',
])]
class FingerprintDeviceCommand extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_SUCCEEDED = 'succeeded';

    public const STATUS_FAILED = 'failed';

    public const COMPARISON_SYNCED = 'synced';

    public const COMPARISON_DIFFERENT = 'different';

    public const COMPARISON_MISSING = 'missing';

    public const COMPARISON_UNKNOWN = 'unknown';

    public const ACTION_UPDATE_USER = 'update_user';

    public const ACTION_DELETE_USER = 'delete_user';

    public const ACTION_QUERY_USER = 'query_user';

    public const ACTION_UPDATE_FINGERPRINT_TEMPLATE = 'update_fingerprint_template';

    public const ACTION_QUERY_FINGERPRINT_TEMPLATE = 'query_fingerprint_template';

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'reply_payload' => 'array',
            'comparison_details' => 'array',
            'sent_at' => 'datetime',
            'replied_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function fingerprintDevice(): BelongsTo
    {
        return $this->belongsTo(FingerprintDevice::class);
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_user_id');
    }

    public function getIclockCommandAttribute(): string
    {
        return "C:{$this->command_id}:{$this->command}";
    }
}
