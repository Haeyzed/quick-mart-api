<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Discount Applicable For Enumeration
 *
 * Defines the scope of products a discount can be applied to.
 *
 * @package App\Enums
 */
enum DiscountApplicableForEnum: string
{
    /**
     * Discount applies to all products.
     */
    case ALL = 'All';

    /**
     * Discount applies only to selected products.
     * The product_list field contains the selected product IDs.
     */
    case SELECTED = 'Selected';

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

