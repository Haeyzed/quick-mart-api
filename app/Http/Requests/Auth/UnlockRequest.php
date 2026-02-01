<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * UnlockRequest
 *
 * Validates incoming unlock (lock screen) data.
 * Requires the current user's password to unlock.
 */
class UnlockRequest extends BaseRequest
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
             * User's current password to verify and unlock.
             *
             * @var string
             * @example myPassword123
             */
            'password' => ['required', 'string'],
        ];
    }
}
