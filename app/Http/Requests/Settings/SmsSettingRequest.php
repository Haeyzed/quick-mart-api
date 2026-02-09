<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;

/**
 * Form request for SMS provider update validation.
 *
 * Validates details (credentials) and active flag.
 */
class SmsSettingRequest extends BaseRequest
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
        ];
    }
}
