<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Customer Type Enumeration
 *
 * Defines the available customer types in the system.
 *
 * @package App\Enums
 */
enum CustomerTypeEnum: string
{
    /**
     * Regular customer type.
     * Represents a registered customer with an account.
     */
    case REGULAR = 'regular';

    /**
     * Walk-in customer type.
     * Represents a one-time or unregistered customer.
     */
    case WALKIN = 'walkin';

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

