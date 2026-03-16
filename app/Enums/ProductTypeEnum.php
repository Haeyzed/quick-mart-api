<?php

declare(strict_types=1);

namespace App\Enums;

enum ProductTypeEnum: string
{
    case STANDARD = 'standard';
    case COMBO = 'combo';
    case DIGITAL = 'digital';
    case SERVICE = 'service';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
