<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class PosSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            'biller_id' => ['nullable', 'integer', 'exists:billers,id'],
            'product_number' => ['nullable', 'integer', 'min:1'],
            'keybord_active' => ['nullable', 'boolean'],
            'is_table' => ['nullable', 'boolean'],
            'send_sms' => ['nullable', 'boolean'],
            'cash_register' => ['nullable', 'boolean'],
            'stripe_public_key' => ['nullable', 'string', 'max:255'],
            'stripe_secret_key' => ['nullable', 'string', 'max:255'],
            'paypal_live_api_username' => ['nullable', 'string', 'max:255'],
            'paypal_live_api_password' => ['nullable', 'string', 'max:255'],
            'paypal_live_api_secret' => ['nullable', 'string', 'max:255'],
            'payment_options' => ['nullable', 'array'],
            'payment_options.*' => ['nullable', 'string', 'max:50'],
            'show_print_invoice' => ['nullable', 'boolean'],
            'invoice_option' => ['nullable', Rule::in(['thermal', 'a4'])],
            'thermal_invoice_size' => ['nullable', 'string', 'max:20'],
        ];
    }
}
