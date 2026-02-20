<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Validator;

/**
 * Class ChangePasswordRequest
 *
 * Handles validation and authorization for changing the authenticated user's password.
 */
class ChangePasswordRequest extends BaseRequest
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
             * User's current password.
             *
             * @example currentPassword123
             */
            'current_password' => ['required', 'string'],

            /**
             * User's new password. Must be at least 8 characters.
             *
             * @example newPassword123
             */
            'password' => ['required', 'string', 'min:8', 'confirmed'],

            /**
             * Confirmation of the new password. Must match password.
             *
             * @example newPassword123
             */
            'password_confirmation' => ['required', 'string'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * Adds an after hook to verify the current password matches the authenticated user's password.
     *
     * @param  Validator  $validator  The validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $user = $this->user();

            if ($user && ! Hash::check($this->current_password, $user->password)) {
                $validator->errors()->add('current_password', 'The current password is incorrect.');
            }
        });
    }
}
