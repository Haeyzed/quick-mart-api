<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class ForgotPasswordRequest
 *
 * Handles validation and authorization for requesting a password reset link.
 */
class ForgotPasswordRequest extends BaseRequest
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
             * User's email address to send the password reset link.
             *
             * @example john.doe@example.com
             */
            'email' => ['required', 'email', 'max:255'],
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
