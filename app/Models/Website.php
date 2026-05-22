<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'url',
    'username',
    'password',
    'category',
    'description',
    'unit_id',
    'is_private',
])]
class Website extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'password' => 'encrypted',
            'is_private' => 'boolean',
        ];
    }

    public function unitModel(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
}
