<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;

/**
 * Form request for HRM setting update validation.
 *
 * Validates checkin and checkout time fields.
 */
class HrmSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkin' => ['required', 'string', 'max:50'],
            'checkout' => ['required', 'string', 'max:50'],
        ];
    }
}
