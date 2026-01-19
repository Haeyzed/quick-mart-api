<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Payment Status Enumeration
 *
 * Defines the available payment statuses for purchases and sales.
 */
enum PaymentStatusEnum: string
{
    /**
     * Fully paid status.
     */
    case PAID = 'paid';

    /**
     * Unpaid status.
     */
    case UNPAID = 'unpaid';

    /**
     * Partially paid status.
     */
    case PARTIAL = 'partial';

    /**
     * Refunded status.
     */
    case REFUNDED = 'refunded';

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
