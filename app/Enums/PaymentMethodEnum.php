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
    case CASH = 'cash';

    /**
     * Credit card payment method.
     */
    case CREDIT_CARD = 'credit card';

    /**
     * Cheque payment method.
     */
    case CHEQUE = 'cheque';

    /**
     * Bank transfer payment method.
     */
    case BANK_TRANSFER = 'bank transfer';

    /**
     * Gift card payment method.
     */
    case GIFT_CARD = 'gift card';

    /**
     * PayPal payment method.
     */
    case PAYPAL = 'paypal';

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
