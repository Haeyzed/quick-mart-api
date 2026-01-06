<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Discount Type Enumeration
 *
 * Defines the available discount types in the system.
 *
 * @package App\Enums
 */
enum DiscountTypeEnum: string
{
    /**
     * Percentage-based discount.
     * The discount value represents a percentage (e.g., 10 means 10%).
     */
    case PERCENTAGE = 'percentage';

    /**
     * Fixed amount discount.
     * The discount value represents a fixed amount (e.g., 10 means $10).
     */
    case FIXED = 'fixed';

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

