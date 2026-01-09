<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Http\Requests\BaseRequest;

/**
 * BrandIndexRequest
 *
 * Validates query parameters for brand index endpoint.
 */
class BrandIndexRequest extends BaseRequest
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
             * Filter brands by active status.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Search term to filter brands by name, description, or slug.
             *
             * @var string|null @search
             * @example apple
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
        $this->merge([
            'per_page' => $this->per_page ? (int) $this->per_page : null,
            'page' => $this->page ? (int) $this->page : null,
            'is_active' => $this->is_active !== null ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
        ]);
    }
}

