<?php

declare(strict_types=1);

namespace App\ViewModels;

/**
 * SMS Model Interface
 *
 * Defines the contract for SMS model implementations.
 * This interface ensures consistent SMS processing across different implementations.
 *
 * @package App\ViewModels
 */
interface ISmsModel
{
    /**
     * Initialize and process SMS sending.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'type': string - Type of SMS (onsite/online)
     *   - 'template_id': int - ID of the SMS template
     *   - 'customer_id': int|array - Customer ID or customer data
     *   - 'reference_no': string - Reference number for the transaction
     *   - 'sale_status': string|int - Sale status
     *   - 'payment_status': string|int - Payment status
     * @return void
     */
    public function initialize(array $data): void;
}

