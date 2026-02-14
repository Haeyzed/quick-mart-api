<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;

/**
 * ProductIndexRequest
 *
 * Validates query parameters for product index endpoint with comprehensive filtering options.
 */
class ProductIndexRequest extends BaseRequest
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
             * Filter by warehouse ID.
             *
             * @var int|null @warehouse_id
             * @example 1
             */
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
            /**
             * Filter by product type (standard, combo, digital, service, all).
             *
             * @var string|null @product_type
             * @example standard
             */
            'product_type' => ['nullable', 'string', 'in:standard,combo,digital,service,all'],
            /**
             * Filter by brand ID.
             *
             * @var int|null @brand_id
             * @example 1
             */
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            /**
             * Filter by category ID.
             *
             * @var int|null @category_id
             * @example 1
             */
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            /**
             * Filter by unit ID.
             *
             * @var int|null @unit_id
             * @example 1
             */
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            /**
             * Filter by tax ID.
             *
             * @var int|null @tax_id
             * @example 1
             */
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            /**
             * Filter by IMEI or variant (imei, variant, or 0).
             *
             * @var string|null @imeiorvariant
             * @example imei
             */
            'imeiorvariant' => ['nullable', 'string', 'in:imei,variant,0'],
            /**
             * Filter by stock status (all, with, without).
             *
             * @var string|null @stock_filter
             * @example with
             */
            'stock_filter' => ['nullable', 'string', 'in:all,with,without'],
            /**
             * Filter by recipe status.
             *
             * @var bool|null @is_recipe
             * @example false
             */
            'is_recipe' => ['nullable', 'boolean'],
            /**
             * Search term to filter products by name, code, variant code, brand, or category.
             *
             * @var string|null @search
             * @example laptop
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
            'per_page' => $this->per_page ? (int)$this->per_page : null,
            'page' => $this->page ? (int)$this->page : null,
            'warehouse_id' => $this->warehouse_id ? (int)$this->warehouse_id : null,
            'brand_id' => $this->brand_id ? (int)$this->brand_id : null,
            'category_id' => $this->category_id ? (int)$this->category_id : null,
            'unit_id' => $this->unit_id ? (int)$this->unit_id : null,
            'tax_id' => $this->tax_id ? (int)$this->tax_id : null,
            'product_type' => $this->product_type ?: 'all',
            'stock_filter' => $this->stock_filter ?: 'all',
            'imeiorvariant' => $this->imeiorvariant ?: '0',
            'is_recipe' => $this->is_recipe !== null ? filter_var($this->is_recipe, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null,
            'search' => $this->search ? trim($this->search) : null,
        ]);
    }
}

