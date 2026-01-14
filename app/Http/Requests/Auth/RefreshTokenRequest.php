<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * RefreshTokenRequest
 *
 * Validates incoming refresh token request.
 */
class RefreshTokenRequest extends BaseRequest
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
             * Whether to revoke the old token after refreshing.
             * If true, the old token will be revoked. If false, both tokens will be valid.
             *
             * @var bool|null
             * @example true
             */
            'revoke_old_token' => ['sometimes', 'boolean'],
        ];
    }
}

