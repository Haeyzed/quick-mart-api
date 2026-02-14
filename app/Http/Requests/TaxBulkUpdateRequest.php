<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * TaxBulkUpdateRequest
 *
 * Validates bulk update request for taxes (activate/deactivate).
 */
class TaxBulkUpdateRequest extends BaseRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Array of tax IDs to update.
             *
             * @var array<int> $ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the ids array must be a valid tax ID.
             *
             * @var int $ids .*
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('taxes', 'id')],
        ];
    }
}

