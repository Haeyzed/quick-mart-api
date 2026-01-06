<?php

declare(strict_types=1);

namespace App\Contracts\Sms;

/**
 * Check Balance Interface
 *
 * Defines the contract for checking SMS provider account balance.
 * SMS providers that support balance checking should implement this interface.
 *
 * @package App\Contracts\Sms
 */
interface CheckBalanceInterface
{
    /**
     * Check the remaining balance in the SMS provider account.
     *
     * @return float|int The remaining balance, or 0 if unable to retrieve
     */
    public function balance(): float|int;
}

