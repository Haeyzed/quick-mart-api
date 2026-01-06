<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IncomeIndexRequest
 *
 * Validates query parameters for income index endpoint.
 */
class IncomeIndexRequest extends FormRequest
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
             * Number of items per page for pagination.
             *
             * @var int|null @per_page
             * @example 10
             */
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            /**
             * Page number for pagination.
             *
             * @var int|null @page
             * @example 1
             */
            'page' => ['nullable', 'integer', 'min:1'],
            /**
             * Start date for filtering incomes (Y-m-d format).
             *
             * @var string|null @starting_date
             * @example 2024-01-01
             */
            'starting_date' => ['nullable', 'date'],
            /**
             * End date for filtering incomes (Y-m-d format).
             *
             * @var string|null @ending_date
             * @example 2024-12-31
             */
            'ending_date' => ['nullable', 'date', 'after_or_equal:starting_date'],
            /**
             * Filter incomes by warehouse ID.
             *
             * @var int|null @warehouse_id
             * @example 1
             */
            'warehouse_id' => ['nullable', 'integer'],
            /**
             * Search term to filter incomes by reference number or date.
             *
             * @var string|null @search
             * @example ir-20240101
             */
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize date formats (convert "/" to "-")
        $startingDate = $this->starting_date ? str_replace('/', '-', $this->starting_date) : null;
        $endingDate = $this->ending_date ? str_replace('/', '-', $this->ending_date) : null;

        $this->merge([
            'starting_date' => $startingDate ? date('Y-m-d', strtotime($startingDate)) : null,
            'ending_date' => $endingDate ? date('Y-m-d', strtotime($endingDate)) : null,
        ]);
    }
}

