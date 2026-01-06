<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Discount Plan Type Enumeration
 *
 * Defines the available discount plan types in the system.
 *
 * @package App\Enums
 */
enum DiscountPlanTypeEnum: string
{
    /**
     * Generic discount plan type.
     * Applies to all customers or products without restrictions.
     */
    case GENERIC = 'generic';

    /**
     * Limited discount plan type.
     * Applies to specific customers or products with restrictions.
     */
    case LIMITED = 'limited';

    /**
     * Get all enum values as an array.
     *
     * @return array<string> Array of all enum values
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

