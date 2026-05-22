<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Hrd = 'hrd';
    case Tu = 'tu';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Hrd => 'HRD',
            self::Tu => 'TU',
        };
    }
}
