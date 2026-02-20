<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class LoginRequest
 *
 * Handles validation and authorization for user login.
 */
class LoginRequest extends BaseRequest
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
             * User's username or email address for login.
             *
             * @example john.doe@example.com or john_doe
             */
            'identifier' => ['required', 'string', 'max:255'],

            /**
             * User's password.
             *
             * @example password123
             */
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., trimming the identifier).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'identifier' => $this->identifier ? trim($this->identifier) : null,
        ]);
    }
}
