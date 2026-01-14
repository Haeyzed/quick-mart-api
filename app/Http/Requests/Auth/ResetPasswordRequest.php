<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ResetPasswordRequest
 *
 * Validates incoming data for password reset.
 */
class ResetPasswordRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            /**
             * User's email address.
             *
             * @var string @email
             * @example john.doe@example.com
             */
            'email' => ['required', 'email', 'max:255'],

            /**
             * Password reset token.
             *
             * @var string @token
             * @example abc123def456...
             */
            'token' => ['required', 'string'],

            /**
             * New password. Must be confirmed.
             *
             * @var string @password
             * @example newpassword123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * Password confirmation. Must match password.
             *
             * @var string @password_confirmation
             * @example newpassword123
             */
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? trim($this->email) : null,
        ]);
    }
}

