<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Hrd = 'hrd';
    case Tu = 'tu';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Hrd => 'HRD',
            self::Tu => 'TU',
            self::Staff => 'Staff',
        };
    }
}
