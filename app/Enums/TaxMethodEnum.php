<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Tax Method Enumeration
 *
 * Defines how tax is calculated and applied.
 */
enum TaxMethodEnum: int
{
    /**
     * Exclusive tax: tax is added on top of the base price.
     * Example: $100 + 10% tax = $110
     */
    case EXCLUSIVE = 1;

    /**
     * Inclusive tax: tax is included in the base price.
     * Example: $110 includes 10% tax, base price is ~$100
     */
    case INCLUSIVE = 2;

    /**
     * Get all enum values as an array.
     *
     * @return array<int> Array of all enum values
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}
