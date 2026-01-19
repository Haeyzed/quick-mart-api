<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Purchase Status Enumeration
 *
 * Defines the available statuses for purchase transactions.
 */
enum PurchaseStatusEnum: string
{
    /**
     * Purchase is pending.
     */
    case PENDING = 'pending';

    /**
     * Purchase is completed/received.
     */
    case COMPLETED = 'completed';

    /**
     * Purchase is cancelled.
     */
    case CANCELLED = 'cancelled';

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
