<?php
declare(strict_types=1);

namespace App\Domain\Enum;

enum UserRole: string
{
    case ADMIN = 'ROLE_ADMIN';
    case MODERATOR = 'ROLE_MODERATOR';
    case MEMBER = 'ROLE_MEMBER';
    case GUEST = 'ROLE_GUEST';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}