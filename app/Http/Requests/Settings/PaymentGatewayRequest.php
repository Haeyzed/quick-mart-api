<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;

/**
 * Form request for payment gateway update validation.
 *
 * Validates details (credentials), active flag, and module_status (ecommerce, pos).
 */
class PaymentGatewayRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'details' => ['nullable', 'array'],
            'details.*' => ['nullable'],
            'active' => ['nullable', 'boolean'],
            'module_status' => ['nullable', 'array'],
            'module_status.ecommerce' => ['nullable', 'boolean'],
            'module_status.pos' => ['nullable', 'boolean'],
        ];
    }
}
