<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;

/**
 * Form request for mail setting update validation.
 *
 * Validates SMTP configuration fields: driver, host, port, from_address, from_name,
 * username, password, encryption, is_default, send_test.
 */
class MailSettingRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'driver' => ['required', 'string', 'max:50'],
            'host' => ['required', 'string', 'max:255'],
            'port' => ['required', 'string', 'max:10'],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:255'],
            'encryption' => ['required', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
            'send_test' => ['nullable', 'boolean'],
        ];
    }
}
