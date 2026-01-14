<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * LoginRequest
 *
 * Validates incoming login data for user authentication.
 */
class LoginRequest extends BaseRequest
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
             * User's name or email address for login.
             *
             * @var string @name
             * @example john.doe@example.com or john_doe
             */
            'name' => ['required', 'string', 'max:255'],

            /**
             * User's password.
             *
             * @var string @password
             * @example password123
             */
            'password' => ['required', 'string'],
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
            'name' => $this->name ? trim($this->name) : null,
        ]);
    }
}

