<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * UpdateProductRequest
 *
 * Form request for updating an existing product.
 */
class UpdateProductRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product') ?? $this->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'code')->ignore($productId),
            ],
            'type' => ['required', 'string', Rule::in(['standard', 'combo', 'digital', 'service'])],
            'barcode_symbology' => ['nullable', 'string', 'max:50'],
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['required', 'integer', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'cost' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'tax_method' => ['nullable', 'integer', Rule::in([0, 1])],
            'alert_quantity' => ['nullable', 'numeric', 'min:0'],
            'image' => ['nullable', 'string'],
            'product_details' => ['nullable', 'string'],
            'is_variant' => ['nullable', 'boolean'],
            'is_imei' => ['nullable', 'boolean'],
            'is_batch' => ['nullable', 'boolean'],
            'is_diffPrice' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'promotion' => ['nullable', 'boolean'],
            'promotion_price' => ['nullable', 'numeric', 'min:0'],
            'starting_date' => ['nullable', 'date'],
            'last_date' => ['nullable', 'date', 'after_or_equal:starting_date'],
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
            'name.required' => 'Product name is required.',
            'code.required' => 'Product code is required.',
            'code.unique' => 'Product code already exists.',
            'type.required' => 'Product type is required.',
            'category_id.required' => 'Category is required.',
            'unit_id.required' => 'Unit is required.',
            'cost.required' => 'Cost is required.',
            'price.required' => 'Price is required.',
        ];
    }
}

