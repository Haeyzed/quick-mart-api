<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Payment Method Enumeration
 *
 * Defines the available payment methods in the system.
 */
enum PaymentMethodEnum: string
{
    /**
     * Cash payment method.
     */
    case CASH = 'Cash';

    /**
     * Credit card payment method.
     */
    case CREDIT_CARD = 'Credit Card';

    /**
     * Cheque payment method.
     */
    case CHEQUE = 'Cheque';

    /**
     * Bank transfer payment method.
     */
    case BANK_TRANSFER = 'Bank Transfer';

    /**
     * Gift card payment method.
     */
    case GIFT_CARD = 'Gift Card';

    /**
     * PayPal payment method.
     */
    case PAYPAL = 'PayPal';

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
