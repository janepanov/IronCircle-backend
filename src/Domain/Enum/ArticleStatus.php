<?php
declare(strict_types=1);

namespace App\Domain\Enum;

enum ArticleStatus: string
{
    case DRAFT = 'DRAFT';
    case PUBLISHED = 'PUBLISHED';
    case ARCHIVED = 'ARCHIVED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function canTransition(self $to): bool
    {
        return match ($this) {
            self::DRAFT => in_array($to, [self::PUBLISHED, self::ARCHIVED], true),
            self::PUBLISHED => $to === self::ARCHIVED,
            self::ARCHIVED => false,
        };
    }
}