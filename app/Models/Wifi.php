<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'ssid',
    'location',
    'ip_address',
    'password',
    'router_type',
    'admin_username',
    'admin_password',
    'link',
    'description',
    'is_private',
])]
class Wifi extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'admin_password' => 'encrypted',
            'is_private' => 'boolean',
        ];
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)
            ->withTimestamps();
    }
}
