<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

/**
 * ChangePasswordRequest
 *
 * Validates incoming password change data.
 */
class ChangePasswordRequest extends BaseRequest
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
             * User's current password.
             *
             * @var string
             * @example currentPassword123
             */
            'current_password' => ['required', 'string'],

            /**
             * User's new password. Must be at least 8 characters.
             *
             * @var string
             * @example newPassword123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * Confirmation of the new password. Must match password.
             *
             * @var string
             * @example newPassword123
             */
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator
     * @return void
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($user && !Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });
    }
}

