<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Reward Point Type Enumeration
 *
 * Defines how reward points are awarded in the system.
 *
 * @package App\Enums
 */
enum RewardPointTypeEnum: string
{
    /**
     * Manual reward point type.
     * Points are manually added by administrators.
     */
    case MANUAL = 'manual';

    /**
     * Automatic reward point type.
     * Points are automatically calculated based on transactions.
     */
    case AUTOMATIC = 'automatic';

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

