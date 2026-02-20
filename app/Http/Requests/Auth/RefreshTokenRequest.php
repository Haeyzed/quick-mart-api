<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;

/**
 * Class RefreshTokenRequest
 *
 * Handles validation and authorization for refreshing the authentication token.
 */
class RefreshTokenRequest extends BaseRequest
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
             * Whether to revoke the old token after refreshing.
             * If true, the old token will be revoked. If false, both tokens will be valid.
             *
             * @example true
             */
            'revoke_old_token' => ['sometimes', 'boolean'],
        ];
    }
}
