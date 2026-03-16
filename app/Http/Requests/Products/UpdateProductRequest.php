<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use App\Enums\ProductTypeEnum;
use App\Enums\TaxMethodEnum;
use App\Models\Product;
use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateProductRequest
 *
 * Handles validation and authorization for updating an existing product.
 */
class UpdateProductRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Product|null $product */
        $product = $this->route('product');

        return [
            /**
             * The name of the product.
             * @example "iPhone 15 Pro"
             */
            'name' => ['sometimes', 'required', 'string', 'max:255'],

            /**
             * Unique product code/SKU.
             * @example "IPH15PRO"
             */
            'code' => ['sometimes', 'required', 'string', 'max:255', Rule::unique('products', 'code')->ignore($product)->withoutTrashed()],

            /**
             * The type of product.
             * @example "standard"
             */
            'type' => ['sometimes', 'required', Rule::enum(ProductTypeEnum::class)],

            /**
             * Barcode symbology to be used.
             * @example "C128"
             */
            'barcode_symbology' => ['sometimes', 'required', 'string'],

            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],

            'cost' => ['nullable', 'numeric', 'min:0'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'profit_margin' => ['nullable', 'numeric'],
            'profit_margin_type' => ['nullable', 'string', 'in:percentage,fixed'],

            'alert_quantity' => ['nullable', 'numeric'],
            'daily_sale_objective' => ['nullable', 'numeric'],

            'promotion' => ['nullable', 'boolean'],
            'promotion_price' => ['nullable', 'numeric'],
            'starting_date' => ['nullable', 'date'],
            'last_date' => ['nullable', 'date', 'after_or_equal:starting_date'],

            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'tax_method' => ['nullable', Rule::enum(TaxMethodEnum::class)],

            'image_paths' => ['nullable', 'array'],
            'image_paths.*' => ['image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'deleted_image_paths' => ['nullable', 'array'],
            'deleted_image_paths.*' => ['string'],

            'file_path' => ['nullable', 'file', 'max:10240'],

            'is_embeded' => ['nullable', 'boolean'],
            'is_batch' => ['nullable', 'boolean'],
            'is_variant' => ['nullable', 'boolean'],
            'is_diff_price' => ['nullable', 'boolean'],
            'is_imei' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'is_online' => ['nullable', 'boolean'],
            'in_stock' => ['nullable', 'boolean'],
            'track_inventory' => ['nullable', 'boolean'],
            'is_sync_disable' => ['nullable', 'boolean'],
            'is_recipe' => ['nullable', 'boolean'],
            'is_addon' => ['nullable', 'boolean'],

            'product_details' => ['nullable', 'array'],
            'short_description' => ['nullable', 'string'],
            'specification' => ['nullable', 'array'],

            'related_products' => ['nullable', 'array'],
            'related_products.*' => ['integer', 'exists:products,id'],

            'extras' => ['nullable', 'array'],
            'menu_type' => ['nullable', 'array'],
            'kitchen_id' => ['nullable', 'integer'],

            'woocommerce_product_id' => ['nullable', 'integer'],
            'woocommerce_media_id' => ['nullable', 'integer'],

            'tags' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string'],
            'meta_description' => ['nullable', 'string'],

            'warranty' => ['nullable', 'integer'],
            'guarantee' => ['nullable', 'integer'],
            'warranty_type' => ['nullable', 'string'],
            'guarantee_type' => ['nullable', 'string'],

            'wastage_percent' => ['nullable', 'numeric'],
            'combo_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'production_cost' => ['nullable', 'numeric'],

            // Structured Array for Variants
            'variants' => ['nullable', 'array'],
            'variants.*.name' => ['required_with:variants', 'string'],
            'variants.*.item_code' => ['nullable', 'string'],
            'variants.*.additional_cost' => ['nullable', 'numeric'],
            'variants.*.additional_price' => ['nullable', 'numeric'],

            // Structured Array for Diff Prices
            'warehouse_prices' => ['nullable', 'array'],
            'warehouse_prices.*.warehouse_id' => ['required_with:warehouse_prices', 'integer', 'exists:warehouses,id'],
            'warehouse_prices.*.price' => ['required_with:warehouse_prices', 'numeric'],

            // Structured Array for Combo Products
            'combo_products' => ['nullable', 'array'],
            'combo_products.*.product_id' => ['required_with:combo_products', 'integer', 'exists:products,id'],
            'combo_products.*.variant_id' => ['nullable', 'integer', 'exists:variants,id'],
            'combo_products.*.qty' => ['required_with:combo_products', 'numeric'],
            'combo_products.*.price' => ['required_with:combo_products', 'numeric'],
            'combo_products.*.wastage_percent' => ['nullable', 'numeric'],
            'combo_products.*.combo_unit_id' => ['nullable', 'integer', 'exists:units,id'],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $booleans = [
            'promotion', 'is_embeded', 'is_batch', 'is_variant', 'is_diff_price',
            'is_imei', 'featured', 'is_active', 'is_online', 'in_stock',
            'track_inventory', 'is_sync_disable', 'is_recipe', 'is_addon'
        ];

        $merge = [];
        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $merge[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            }
        }

        // Apply defaults based on legacy logic
        if (!isset($merge['is_embeded']) && $this->has('is_embeded')) {
            $merge['is_embeded'] = false;
        }

        if (!isset($merge['featured']) && $this->has('featured')) {
            $merge['featured'] = false;
        }

        if (!empty($merge)) {
            $this->merge($merge);
        }
    }
}
