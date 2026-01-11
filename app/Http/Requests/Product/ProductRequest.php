<?php

declare(strict_types=1);

namespace App\Http\Requests\Product;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * ProductRequest
 *
 * Validates incoming data for both creating and updating products.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class ProductRequest extends BaseRequest
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
        $productId = $this->route('product')?->id ?? $this->route('product') ?? $this->route('id');

        return [
            // Basic product information
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'code')->ignore($productId)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            'type' => ['required', 'string', Rule::in(['standard', 'combo', 'digital', 'service'])],
            'slug' => ['nullable', 'string', 'max:255'],
            'barcode_symbology' => ['nullable', 'string', Rule::in(['C128', 'C39', 'UPCA', 'UPCE', 'EAN8', 'EAN13'])],
            
            // Relations
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'tax_method' => ['nullable', 'integer', Rule::in([0, 1])],
            'kitchen_id' => ['nullable', 'integer', 'exists:kitchens,id'],
            
            // Pricing
            'cost' => ['nullable', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            'profit_margin' => ['nullable', 'numeric'],
            'profit_margin_type' => ['nullable', 'string', Rule::in(['percentage', 'flat'])],
            
            // Inventory
            'qty' => ['nullable', 'numeric', 'min:0'],
            'alert_quantity' => ['nullable', 'numeric', 'min:0'],
            'track_inventory' => ['nullable', 'boolean'],
            
            // Promotion
            'promotion' => ['nullable', 'boolean'],
            'promotion_price' => ['nullable', 'numeric', 'min:0'],
            'starting_date' => ['nullable', 'date'],
            'last_date' => ['nullable', 'date', 'after_or_equal:starting_date'],
            
            // Images and files - accept array of files and previous images as strings
            'prev_img' => ['nullable', 'array'], // Previous image names (strings) - only for update
            'prev_img.*' => ['nullable', 'string'],
            'image' => ['nullable', 'array'], // New images (files)
            'image.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'file' => ['nullable', 'file', 'max:10240'], // For digital products
            
            // Product details
            'product_details' => ['nullable', 'string'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'specification' => ['nullable', 'string'],
            
            // Features
            'is_variant' => ['nullable', 'boolean'],
            'is_batch' => ['nullable', 'boolean'],
            'is_imei' => ['nullable', 'boolean'],
            'is_diffPrice' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'is_online' => ['nullable', 'boolean'],
            'in_stock' => ['nullable', 'boolean'],
            'is_addon' => ['nullable', 'boolean'],
            'is_recipe' => ['nullable', 'boolean'],
            'is_embeded' => ['nullable', 'boolean'],
            'is_sync_disable' => ['nullable', 'boolean'],
            
            // Variants - arrays
            'variant_option' => ['nullable', 'array'],
            'variant_option.*' => ['nullable', 'string'],
            'variant_value' => ['nullable', 'array'],
            'variant_value.*' => ['nullable', 'string'],
            'variant_name' => ['nullable', 'array', 'required_if:is_variant,1'],
            'variant_name.*' => ['nullable', 'string'],
            'item_code' => ['nullable', 'array'],
            'item_code.*' => ['nullable', 'string', 'max:255'],
            'additional_cost' => ['nullable', 'array'],
            'additional_cost.*' => ['nullable', 'numeric', 'min:0'],
            'additional_price' => ['nullable', 'array'],
            'additional_price.*' => ['nullable', 'numeric', 'min:0'],
            
            // Combo/Recipe products - arrays
            'product_id' => ['nullable', 'array', 'required_if:type,combo'],
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            'variant_id' => ['nullable', 'array'],
            'variant_id.*' => ['nullable', 'integer', 'exists:product_variants,id'],
            'product_qty' => ['nullable', 'array'],
            'product_qty.*' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'array'],
            'unit_price.*' => ['nullable', 'numeric', 'min:0'],
            'wastage_percent' => ['nullable', 'array'],
            'wastage_percent.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'combo_unit_id' => ['nullable', 'array'],
            'combo_unit_id.*' => ['nullable', 'integer', 'exists:units,id'],
            
            // Initial stock - arrays
            'is_initial_stock' => ['nullable', 'boolean'],
            'stock_warehouse_id' => ['nullable', 'array', 'required_if:is_initial_stock,1'],
            'stock_warehouse_id.*' => ['nullable', 'integer', 'exists:warehouses,id'],
            'stock' => ['nullable', 'array'],
            'stock.*' => ['nullable', 'numeric', 'min:0'],
            
            // Different prices per warehouse - arrays
            'warehouse_id' => ['nullable', 'array'],
            'warehouse_id.*' => ['nullable', 'integer', 'exists:warehouses,id'],
            'diff_price' => ['nullable', 'array'],
            'diff_price.*' => ['nullable', 'numeric', 'min:0'],
            
            // Warranty and guarantee
            'warranty' => ['nullable', 'integer', 'min:0'],
            'warranty_type' => ['nullable', 'string', 'required_with:warranty', Rule::in(['days', 'months', 'years'])],
            'guarantee' => ['nullable', 'integer', 'min:0'],
            'guarantee_type' => ['nullable', 'string', 'required_with:guarantee', Rule::in(['days', 'months', 'years'])],
            
            // Ecommerce fields
            'related_products' => ['nullable', 'string'],
            'tags' => ['nullable', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            
            // Restaurant fields
            'menu_type' => ['nullable', 'array'],
            'menu_type.*' => ['nullable', 'integer', 'exists:menu_type,id'],
            'extras' => ['nullable', 'string'],
            'daily_sale_objective' => ['nullable', 'numeric', 'min:0'],
            
            // Production
            'production_cost' => ['nullable', 'numeric', 'min:0'],
            
            // WooCommerce
            'woocommerce_product_id' => ['nullable', 'integer'],
            'woocommerce_media_id' => ['nullable', 'integer'],
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
            'code.unique' => 'Product code already exists. Please use a different code.',
            'type.required' => 'Product type is required.',
            'type.in' => 'Product type must be one of: standard, combo, digital, or service.',
            'category_id.required' => 'Category is required.',
            'category_id.exists' => 'The selected category does not exist.',
            'price.required' => 'Product price is required.',
            'price.numeric' => 'Product price must be a number.',
            'price.min' => 'Product price must be at least 0.',
            'variant_name.required_if' => 'Variant names are required when product has variants.',
            'product_id.required_if' => 'Products are required for combo type.',
            'stock_warehouse_id.required_if' => 'Warehouse is required when setting initial stock.',
            'image.*.image' => 'All uploaded files must be images.',
            'image.*.mimes' => 'Images must be in JPEG, PNG, JPG, GIF, or WebP format.',
            'image.*.max' => 'Each image must not exceed 5MB.',
            'file.max' => 'The file must not exceed 10MB.',
            'last_date.after_or_equal' => 'End date must be after or equal to start date.',
            'warranty_type.required_with' => 'Warranty type is required when warranty is set.',
            'guarantee_type.required_with' => 'Guarantee type is required when guarantee is set.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Handle boolean fields from FormData (they come as strings "true"/"false")
        $booleanFields = [
            'is_variant', 'is_batch', 'is_imei', 'is_diffPrice', 'is_active',
            'featured', 'promotion', 'is_online', 'in_stock', 'is_addon',
            'is_recipe', 'is_embeded', 'is_sync_disable', 'is_initial_stock',
            'track_inventory'
        ];
        
        $mergeData = [];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);
                if ($value !== null) {
                    $mergeData[$field] = filter_var(
                        $value,
                        FILTER_VALIDATE_BOOLEAN,
                        FILTER_NULL_ON_FAILURE
                    );
                }
            }
        }
        
        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }
}

