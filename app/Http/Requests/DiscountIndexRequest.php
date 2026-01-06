<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * DiscountIndexRequest
 *
 * Validates query parameters for discount index endpoint.
 */
class DiscountIndexRequest extends FormRequest
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
             * Number of items to return per page.
             *
             * @var int|null @per_page
             * @example 10
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            /**
             * The page number for pagination.
             *
             * @var int|null @page
             * @example 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Filter discounts by active status.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Filter discounts by type (percentage or fixed).
             *
             * @var string|null @type
             * @example percentage
             */
            'type' => ['nullable', 'string', 'in:percentage,fixed'],
            /**
             * Filter discounts by applicable_for (All or Selected).
             *
             * @var string|null @applicable_for
             * @example All
             */
            'applicable_for' => ['nullable', 'string', 'in:All,Selected'],
            /**
             * Search term to filter discounts by name.
             *
             * @var string|null @search
             * @example summer
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}

