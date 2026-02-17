<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

/**
 * Form request for creating a customer.
 * Uses same validation rules as CustomerRequest (route customer is null on store).
 */
class StoreCustomerRequest extends CustomerRequest
{
    //
}
