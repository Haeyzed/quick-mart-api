<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * DiscountBulkDestroyRequest
 *
 * Validates bulk delete request for discounts.
 */
class DiscountBulkDestroyRequest extends FormRequest
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
             * Array of discount IDs to be deleted.
             *
             * @var array<int> @ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Each ID in the array must be an integer and exist in the discounts table.
             *
             * @var int @ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('discounts', 'id')],
        ];
    }
}

