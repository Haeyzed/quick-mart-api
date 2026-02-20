<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class UnlockRequest
 *
 * Handles validation and authorization for unlocking the screen (requires current password).
 */
class UnlockRequest extends BaseRequest
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
             * User's current password to verify and unlock.
             *
             * @example myPassword123
             */
            'password' => ['required', 'string'],
        ];
    }
}
