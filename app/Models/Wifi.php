<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
])]
class Wifi extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'admin_password' => 'encrypted',
        ];
    }
}
