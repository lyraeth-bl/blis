<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'name',
    'url',
    'username',
    'password',
    'category',
    'description',
])]
class Website extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
        ];
    }
}
