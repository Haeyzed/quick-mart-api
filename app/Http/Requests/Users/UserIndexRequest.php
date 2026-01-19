<?php

declare(strict_types=1);

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * UserIndexRequest
 *
 * Validates incoming data for user listing/index endpoints.
 * Supports filtering and pagination.
 */
class UserIndexRequest extends BaseRequest
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
             * Filter users by active status.
             *
             * @var bool|null $is_active
             * @example true
             */
            'is_active' => [
                'nullable',
                'boolean',
            ],

            /**
             * Search term to filter users by name or email.
             *
             * @var string|null $search
             * @example john
             */
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            /**
             * Number of items per page for pagination.
             *
             * @var int|null $per_page
             * @example 15
             */
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ];
    }
}
