<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class ResetPasswordRequest
 *
 * Handles validation and authorization for resetting a user's password.
 */
class ResetPasswordRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * User's email address.
             *
             * @example john.doe@example.com
             */
            'email' => ['required', 'email', 'max:255'],

            /**
             * Password reset token from the reset link.
             *
             * @example abc123def456...
             */
            'token' => ['required', 'string'],

            /**
             * New password. Must be confirmed.
             *
             * @example newpassword123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * Password confirmation. Must match password.
             *
             * @example newpassword123
             */
            'password_confirmation' => ['required', 'string', 'min:8'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., trimming the email).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email ? trim($this->email) : null,
        ]);
    }
}
