<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * ProductHistoryRequest
 *
 * Validates incoming data for product history endpoints (sales, purchases, returns, adjustments, transfers).
 * Supports filtering by warehouse, date range, search term, and pagination.
 */
class ProductHistoryRequest extends BaseRequest
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
             * Filter history by specific warehouse. If not provided, returns history for all warehouses.
             *
             * @var int|null $warehouse_id
             * @example 1
             */
            'warehouse_id' => [
                'nullable',
                'integer',
                'exists:warehouses,id',
            ],

            /**
             * Start date for the history query date range. Must be a valid date format.
             * If not provided, defaults to one year ago from today.
             *
             * @var string|null $starting_date
             * @example 2024-01-01
             */
            'starting_date' => [
                'nullable',
                'date',
            ],

            /**
             * End date for the history query date range. Must be a valid date format.
             * Must be equal to or after the starting_date if both are provided.
             * If not provided, defaults to today's date.
             *
             * @var string|null $ending_date
             * @example 2024-12-31
             */
            'ending_date' => [
                'nullable',
                'date',
                'after_or_equal:starting_date',
            ],

            /**
             * Search term to filter history by reference number or date.
             * Searches within transaction reference numbers and matches date formats.
             * Only applicable for sale, purchase, and return history endpoints.
             *
             * @var string|null $search
             * @example pr-20241225-123456
             */
            'search' => [
                'nullable',
                'string',
            ],

            /**
             * Maximum number of records to return per page.
             * Must be between 1 and 100. Default is 10 if not provided.
             * Only applicable for paginated history endpoints (sales, purchases, returns).
             *
             * @var int|null $limit
             * @example 25
             */
            'limit' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],

            /**
             * Number of records to skip for pagination (offset).
             * Must be 0 or greater. Default is 0 if not provided.
             * Only applicable for paginated history endpoints (sales, purchases, returns).
             *
             * @var int|null $offset
             * @example 0
             */
            'offset' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'warehouse_id.exists' => 'The selected warehouse does not exist.',
            'starting_date.date' => 'The starting date must be a valid date.',
            'ending_date.date' => 'The ending date must be a valid date.',
            'ending_date.after_or_equal' => 'The ending date must be equal to or after the starting date.',
            'limit.min' => 'The limit must be at least 1.',
            'limit.max' => 'The limit cannot exceed 100.',
            'offset.min' => 'The offset must be 0 or greater.',
        ];
    }
}
