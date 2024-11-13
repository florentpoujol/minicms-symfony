<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportedLocale: string
{
    case fr = 'fr';
    case en = 'en';

    /**
     * @return array<string>
     */
    public static function getLocales(): array
    {
        return array_map(fn (self $c): string => $c->value, self::cases());
    }
}