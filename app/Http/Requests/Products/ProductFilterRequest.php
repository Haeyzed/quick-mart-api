<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Arr;

/**
 * Class ProductFilterRequest
 *
 * Validates and normalizes filter parameters for the products listing endpoint.
 */
class ProductFilterRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $arrayFields = ['is_active', 'featured', 'type', 'brand_id', 'category_id', 'unit_id'];

        foreach ($arrayFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);

                if (is_string($value)) {
                    $this->merge([
                        $field => array_values(array_filter(
                            explode(',', $value),
                            fn ($val) => trim((string) $val) !== ''
                        )),
                    ]);
                } elseif (! is_array($value)) {
                    $this->merge([
                        $field => Arr::wrap($value),
                    ]);
                }
            }
        }
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
             * Search term to filter products by name or code.
             *
             * @example "iPhone"
             */
            'search' => ['nullable', 'string'],

            /**
             * Filter by active status.
             */
            'is_active' => ['nullable', 'array'],
            'is_active.*' => ['boolean'],

            /**
             * Filter by featured status.
             */
            'featured' => ['nullable', 'array'],
            'featured.*' => ['boolean'],

            /**
             * Filter by product type.
             */
            'type' => ['nullable', 'array'],
            'type.*' => ['string', 'in:standard,combo,digital,service'],

            /**
             * Filter by brand ID.
             */
            'brand_id' => ['nullable', 'array'],
            'brand_id.*' => ['integer', 'exists:brands,id'],

            /**
             * Filter by category ID.
             */
            'category_id' => ['nullable', 'array'],
            'category_id.*' => ['integer', 'exists:categories,id'],

            /**
             * Filter by unit ID.
             */
            'unit_id' => ['nullable', 'array'],
            'unit_id.*' => ['integer', 'exists:units,id'],

            /**
             * Filter by warehouse ID to see stock.
             */
            'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],

            /**
             * Stock filter (all, with_stock, without_stock).
             */
            'stock_filter' => ['nullable', 'string', 'in:all,with_stock,without_stock'],

            /**
             * Amount of items to return per page for pagination.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
