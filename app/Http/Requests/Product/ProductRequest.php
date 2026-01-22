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
     * Validates all product fields including:
     * - Basic information: name, code, type, slug, barcode_symbology
     * - Relations: brand_id, category_id, unit_id, tax_id, kitchen_id
     * - Pricing: cost, price, wholesale_price, profit_margin, profit_margin_type
     * - Inventory: qty, alert_quantity, track_inventory
     * - Promotion: promotion, promotion_price, starting_date, last_date
     * - Images and files: prev_img, image, file
     * - Product details: product_details, short_description, specification
     * - Features: is_variant, is_batch, is_imei, is_diff_price, is_active, featured, etc.
     * - Variants: variant_option, variant_value, variant_name, item_code, additional_cost, additional_price
     * - Combo/Recipe: product_id, variant_id, product_qty, unit_price, wastage_percent, combo_unit_id
     * - Initial stock: is_initial_stock, stock_warehouse_id, stock
     * - Warehouse prices: warehouse_id, diff_price
     * - Warranty/Guarantee: warranty, warranty_type, guarantee, guarantee_type
     * - Ecommerce: related_products, tags, meta_title, meta_description
     * - Restaurant: menu_type, extras, daily_sale_objective
     * - Production: production_cost
     * - WooCommerce: woocommerce_product_id, woocommerce_media_id
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id ?? $this->route('product') ?? $this->route('id');

        return [
            /**
             * The product name. Must be unique and required.
             *
             * @var string $name
             * @example Smartphone XYZ
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Product code/SKU. Must be unique among active products.
             *
             * @var string|null $code
             * @example PROD-001
             */
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('products', 'code')->ignore($productId)->where(function ($query) {
                    return $query->where('is_active', 1);
                }),
            ],
            /**
             * Product type. Must be one of: standard, combo, digital, or service.
             *
             * @var string $type
             * @example standard
             */
            'type' => ['required', 'string', Rule::in(['standard', 'combo', 'digital', 'service'])],
            /**
             * URL-friendly slug for the product. Auto-generated from name if not provided.
             *
             * @var string|null $slug
             * @example smartphone-xyz
             */
            'slug' => ['nullable', 'string', 'max:255'],
            /**
             * Barcode symbology type. Must be one of: C128, C39, UPCA, UPCE, EAN8, or EAN13.
             *
             * @var string|null $barcode_symbology
             * @example EAN13
             */
            'barcode_symbology' => ['nullable', 'string', Rule::in(['C128', 'C39', 'UPCA', 'UPCE', 'EAN8', 'EAN13'])],
            
            /**
             * Brand ID. Must exist in brands table.
             *
             * @var int|null $brand_id
             * @example 1
             */
            'brand_id' => ['nullable', 'integer', 'exists:brands,id'],
            /**
             * Category ID. Required and must exist in categories table.
             *
             * @var int $category_id
             * @example 5
             */
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            /**
             * Base unit ID. Must exist in units table.
             *
             * @var int|null $unit_id
             * @example 2
             */
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            /**
             * Purchase unit ID. Must exist in units table.
             *
             * @var int|null $purchase_unit_id
             * @example 3
             */
            'purchase_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            /**
             * Sale unit ID. Must exist in units table.
             *
             * @var int|null $sale_unit_id
             * @example 3
             */
            'sale_unit_id' => ['nullable', 'integer', 'exists:units,id'],
            /**
             * Tax ID. Must exist in taxes table.
             *
             * @var int|null $tax_id
             * @example 1
             */
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            /**
             * Tax method. 1 for Exclusive, 2 for Inclusive.
             *
             * @var int|null $tax_method
             * @example 1
             */
            'tax_method' => ['nullable', 'integer', Rule::in([1, 2])],
            /**
             * Kitchen ID for restaurant module. Must exist in kitchens table.
             *
             * @var int|null $kitchen_id
             * @example 1
             */
            'kitchen_id' => ['nullable', 'integer', 'exists:kitchens,id'],
            
            /**
             * Product cost price. Must be numeric and non-negative.
             *
             * @var float|null $cost
             * @example 50.00
             */
            'cost' => ['nullable', 'numeric', 'min:0'],
            /**
             * Product selling price. Required, must be numeric and non-negative.
             *
             * @var float $price
             * @example 75.00
             */
            'price' => ['required', 'numeric', 'min:0'],
            /**
             * Wholesale price. Must be numeric and non-negative.
             *
             * @var float|null $wholesale_price
             * @example 60.00
             */
            'wholesale_price' => ['nullable', 'numeric', 'min:0'],
            /**
             * Profit margin amount. Must be numeric.
             *
             * @var float|null $profit_margin
             * @example 25.00
             */
            'profit_margin' => ['nullable', 'numeric'],
            /**
             * Profit margin type. Must be 'percentage' or 'flat'.
             *
             * @var string|null $profit_margin_type
             * @example percentage
             */
            'profit_margin_type' => ['nullable', 'string', Rule::in(['percentage', 'flat'])],
            
            /**
             * Current stock quantity. Must be numeric and non-negative.
             *
             * @var float|null $qty
             * @example 100.00
             */
            'qty' => ['nullable', 'numeric', 'min:0'],
            /**
             * Alert quantity threshold for low stock notifications. Must be numeric and non-negative.
             *
             * @var float|null $alert_quantity
             * @example 10.00
             */
            'alert_quantity' => ['nullable', 'numeric', 'min:0'],
            /**
             * Whether to track inventory for this product.
             *
             * @var bool|null $track_inventory
             * @example true
             */
            'track_inventory' => ['nullable', 'boolean'],
            
            /**
             * Whether the product is currently on promotion.
             *
             * @var bool|null $promotion
             * @example true
             */
            'promotion' => ['nullable', 'boolean'],
            /**
             * Promotion price. Must be numeric and non-negative.
             *
             * @var float|null $promotion_price
             * @example 60.00
             */
            'promotion_price' => ['nullable', 'numeric', 'min:0'],
            /**
             * Promotion start date. Must be a valid date.
             *
             * @var string|null $starting_date
             * @example 2024-01-01
             */
            'starting_date' => ['nullable', 'date'],
            /**
             * Promotion end date. Must be a valid date and after or equal to starting_date.
             *
             * @var string|null $last_date
             * @example 2024-12-31
             */
            'last_date' => ['nullable', 'date', 'after_or_equal:starting_date'],
            
            /**
             * Previous image filenames array (for update operations). Array of string filenames.
             *
             * @var array<string>|null $prev_img
             * @example ['image1.jpg', 'image2.jpg']
             */
            'prev_img' => ['nullable', 'array'],
            /**
             * Previous image filename (individual item).
             *
             * @var string|null $prev_img.*
             * @example image1.jpg
             */
            'prev_img.*' => ['nullable', 'string'],
            /**
             * New product images to upload. Array of image files. Accepts JPEG, PNG, JPG, GIF, or WebP. Max 5MB each.
             *
             * @var array<\Illuminate\Http\UploadedFile>|null $image
             * @example [UploadedFile, UploadedFile]
             */
            'image' => ['nullable', 'array'],
            /**
             * New product image (individual file). Accepts JPEG, PNG, JPG, GIF, or WebP. Max 5MB.
             *
             * @var \Illuminate\Http\UploadedFile|null $image.*
             * @example UploadedFile
             */
            'image.*' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            /**
             * Digital product file. Max 10MB.
             *
             * @var \Illuminate\Http\UploadedFile|null $file
             * @example digital-product.pdf
             */
            'file' => ['nullable', 'file', 'max:10240'],
            
            /**
             * Full product description/details. Long text content.
             *
             * @var json|null $product_details
             * @example This is a detailed description of the product...
             */
            'product_details' => ['nullable', 'json'],
            /**
             * Short product description. Max 1000 characters.
             *
             * @var string|null $short_description
             * @example High-quality product with excellent features
             */
            'short_description' => ['nullable', 'string', 'max:1000'],
            /**
             * Product specifications. Technical details and specifications.
             *
             * @var string|null $specification
             * @example Dimensions: 10x5x2cm, Weight: 200g
             */
            'specification' => ['nullable', 'string'],
            
            /**
             * Whether the product has variants.
             *
             * @var bool|null $is_variant
             * @example true
             */
            'is_variant' => ['nullable', 'boolean'],
            /**
             * Whether the product uses batch tracking.
             *
             * @var bool|null $is_batch
             * @example false
             */
            'is_batch' => ['nullable', 'boolean'],
            /**
             * Whether the product uses IMEI tracking.
             *
             * @var bool|null $is_imei
             * @example false
             */
            'is_imei' => ['nullable', 'boolean'],
            /**
             * Whether the product has different prices per warehouse.
             *
             * @var bool|null $is_diff_price
             * @example false
             */
            'is_diff_price' => ['nullable', 'boolean'],
            /**
             * Whether the product is active and visible.
             *
             * @var bool|null $is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Whether the product is featured.
             *
             * @var bool|null $featured
             * @example false
             */
            'featured' => ['nullable', 'boolean'],
            /**
             * Whether the product is available online.
             *
             * @var bool|null $is_online
             * @example true
             */
            'is_online' => ['nullable', 'boolean'],
            /**
             * Whether the product is currently in stock.
             *
             * @var bool|null $in_stock
             * @example true
             */
            'in_stock' => ['nullable', 'boolean'],
            /**
             * Whether the product is an addon.
             *
             * @var bool|null $is_addon
             * @example false
             */
            'is_addon' => ['nullable', 'boolean'],
            /**
             * Whether the product is a recipe/product combination.
             *
             * @var bool|null $is_recipe
             * @example false
             */
            'is_recipe' => ['nullable', 'boolean'],
            /**
             * Whether the product is embedded.
             *
             * @var bool|null $is_embeded
             * @example false
             */
            'is_embeded' => ['nullable', 'boolean'],
            /**
             * Whether WooCommerce sync is disabled for this product.
             *
             * @var bool|null $is_sync_disable
             * @example false
             */
            'is_sync_disable' => ['nullable', 'boolean'],
            
            /**
             * Variant option names array. Used for product variants.
             *
             * @var array<string>|null $variant_option
             * @example ['Color', 'Size']
             */
            'variant_option' => ['nullable', 'array'],
            /**
             * Variant option name (individual item).
             *
             * @var string|null $variant_option.*
             * @example Color
             */
            'variant_option.*' => ['nullable', 'string'],
            /**
             * Variant option values array. Used for product variants.
             *
             * @var array<string>|null $variant_value
             * @example ['Red,Blue', 'Small,Medium,Large']
             */
            'variant_value' => ['nullable', 'array'],
            /**
             * Variant option value (individual item).
             *
             * @var string|null $variant_value.*
             * @example Red,Blue
             */
            'variant_value.*' => ['nullable', 'string'],
            /**
             * Variant names array. Required if is_variant is true.
             *
             * @var array<string>|null $variant_name
             * @example ['Red Small', 'Red Medium', 'Blue Large']
             */
            'variant_name' => ['nullable', 'array', 'required_if:is_variant,1'],
            /**
             * Variant name (individual item).
             *
             * @var string|null $variant_name.*
             * @example Red Small
             */
            'variant_name.*' => ['nullable', 'string'],
            /**
             * Item codes array for variants. Max 255 characters each.
             *
             * @var array<string>|null $item_code
             * @example ['VAR-001', 'VAR-002']
             */
            'item_code' => ['nullable', 'array'],
            /**
             * Item code for variant (individual item). Max 255 characters.
             *
             * @var string|null $item_code.*
             * @example VAR-001
             */
            'item_code.*' => ['nullable', 'string', 'max:255'],
            /**
             * Additional cost per variant array. Must be numeric and non-negative.
             *
             * @var array<float>|null $additional_cost
             * @example [5.00, 10.00]
             */
            'additional_cost' => ['nullable', 'array'],
            /**
             * Additional cost for variant (individual item). Must be numeric and non-negative.
             *
             * @var float|null $additional_cost.*
             * @example 5.00
             */
            'additional_cost.*' => ['nullable', 'numeric', 'min:0'],
            /**
             * Additional price per variant array. Must be numeric and non-negative.
             *
             * @var array<float>|null $additional_price
             * @example [10.00, 15.00]
             */
            'additional_price' => ['nullable', 'array'],
            /**
             * Additional price for variant (individual item). Must be numeric and non-negative.
             *
             * @var float|null $additional_price.*
             * @example 10.00
             */
            'additional_price.*' => ['nullable', 'numeric', 'min:0'],
            
            /**
             * Combo product IDs array. Required if type is 'combo'. Must exist in products table.
             *
             * @var array<int>|null $product_id
             * @example [1, 2, 3]
             */
            'product_id' => ['nullable', 'array', 'required_if:type,combo'],
            /**
             * Combo product ID (individual item). Must exist in products table.
             *
             * @var int|null $product_id.*
             * @example 1
             */
            'product_id.*' => ['nullable', 'integer', 'exists:products,id'],
            /**
             * Combo product variant IDs array. Must exist in product_variants table.
             *
             * @var array<int>|null $variant_id
             * @example [5, 6]
             */
            'variant_id' => ['nullable', 'array'],
            /**
             * Combo product variant ID (individual item). Must exist in product_variants table.
             *
             * @var int|null $variant_id.*
             * @example 5
             */
            'variant_id.*' => ['nullable', 'integer', 'exists:product_variants,id'],
            /**
             * Combo product quantities array. Must be numeric and non-negative.
             *
             * @var array<float>|null $product_qty
             * @example [2.00, 1.50]
             */
            'product_qty' => ['nullable', 'array'],
            /**
             * Combo product quantity (individual item). Must be numeric and non-negative.
             *
             * @var float|null $product_qty.*
             * @example 2.00
             */
            'product_qty.*' => ['nullable', 'numeric', 'min:0'],
            /**
             * Combo product unit prices array. Must be numeric and non-negative.
             *
             * @var array<float>|null $unit_price
             * @example [25.00, 30.00]
             */
            'unit_price' => ['nullable', 'array'],
            /**
             * Combo product unit price (individual item). Must be numeric and non-negative.
             *
             * @var float|null $unit_price.*
             * @example 25.00
             */
            'unit_price.*' => ['nullable', 'numeric', 'min:0'],
            /**
             * Wastage percentages array for combo products. Must be numeric, between 0 and 100.
             *
             * @var array<float>|null $wastage_percent
             * @example [5.00, 10.00]
             */
            'wastage_percent' => ['nullable', 'array'],
            /**
             * Wastage percent for combo product (individual item). Must be numeric, between 0 and 100.
             *
             * @var float|null $wastage_percent.*
             * @example 5.00
             */
            'wastage_percent.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
            /**
             * Combo product unit IDs array. Must exist in units table.
             *
             * @var array<int>|null $combo_unit_id
             * @example [2, 3]
             */
            'combo_unit_id' => ['nullable', 'array'],
            /**
             * Combo product unit ID (individual item). Must exist in units table.
             *
             * @var int|null $combo_unit_id.*
             * @example 2
             */
            'combo_unit_id.*' => ['nullable', 'integer', 'exists:units,id'],
            
            /**
             * Whether to set initial stock for this product.
             *
             * @var bool|null $is_initial_stock
             * @example true
             */
            'is_initial_stock' => ['nullable', 'boolean'],
            /**
             * Warehouse IDs array for initial stock. Required if is_initial_stock is true. Must exist in warehouses table.
             *
             * @var array<int>|null $stock_warehouse_id
             * @example [1, 2]
             */
            'stock_warehouse_id' => ['nullable', 'array', 'required_if:is_initial_stock,1'],
            /**
             * Warehouse ID for initial stock (individual item). Must exist in warehouses table.
             *
             * @var int|null $stock_warehouse_id.*
             * @example 1
             */
            'stock_warehouse_id.*' => ['nullable', 'integer', 'exists:warehouses,id'],
            /**
             * Initial stock quantities array per warehouse. Must be numeric and non-negative.
             *
             * @var array<float>|null $stock
             * @example [100.00, 50.00]
             */
            'stock' => ['nullable', 'array'],
            /**
             * Initial stock quantity per warehouse (individual item). Must be numeric and non-negative.
             *
             * @var float|null $stock.*
             * @example 100.00
             */
            'stock.*' => ['nullable', 'numeric', 'min:0'],
            
            /**
             * Warehouse IDs array for different prices. Must exist in warehouses table.
             *
             * @var array<int>|null $warehouse_id
             * @example [1, 3]
             */
            'warehouse_id' => ['nullable', 'array'],
            /**
             * Warehouse ID for different price (individual item). Must exist in warehouses table.
             *
             * @var int|null $warehouse_id.*
             * @example 1
             */
            'warehouse_id.*' => ['nullable', 'integer', 'exists:warehouses,id'],
            /**
             * Different prices array per warehouse. Must be numeric and non-negative.
             *
             * @var array<float>|null $diff_price
             * @example [70.00, 80.00]
             */
            'diff_price' => ['nullable', 'array'],
            /**
             * Different price per warehouse (individual item). Must be numeric and non-negative.
             *
             * @var float|null $diff_price.*
             * @example 70.00
             */
            'diff_price.*' => ['nullable', 'numeric', 'min:0'],
            
            /**
             * Warranty period in days/months/years. Must be a non-negative integer.
             *
             * @var int|null $warranty
             * @example 365
             */
            'warranty' => ['nullable', 'integer', 'min:0'],
            /**
             * Warranty type. Required if warranty is set. Must be 'days', 'months', or 'years'.
             *
             * @var string|null $warranty_type
             * @example days
             */
            'warranty_type' => ['nullable', 'string', 'required_with:warranty', Rule::in(['days', 'months', 'years'])],
            /**
             * Guarantee period in days/months/years. Must be a non-negative integer.
             *
             * @var int|null $guarantee
             * @example 30
             */
            'guarantee' => ['nullable', 'integer', 'min:0'],
            /**
             * Guarantee type. Required if guarantee is set. Must be 'days', 'months', or 'years'.
             *
             * @var string|null $guarantee_type
             * @example days
             */
            'guarantee_type' => ['nullable', 'string', 'required_with:guarantee', Rule::in(['days', 'months', 'years'])],
            
            /**
             * Comma-separated list of related product IDs for ecommerce.
             *
             * @var string|null $related_products
             * @example 1,2,3
             */
            'related_products' => ['nullable', 'string'],
            /**
             * Product tags. Comma-separated or space-separated tags.
             *
             * @var string|null $tags
             * @example electronics,smartphone,new
             */
            'tags' => ['nullable', 'string'],
            /**
             * SEO meta title. Max 255 characters.
             *
             * @var string|null $meta_title
             * @example Best Smartphone 2024 - Buy Now
             */
            'meta_title' => ['nullable', 'string', 'max:255'],
            /**
             * SEO meta description. Max 1000 characters.
             *
             * @var string|null $meta_description
             * @example Shop the best smartphones with great deals...
             */
            'meta_description' => ['nullable', 'string', 'max:1000'],
            
            /**
             * Menu type IDs array for restaurant module. Must exist in menu_type table.
             *
             * @var array<int>|null $menu_type
             * @example [1, 2]
             */
            'menu_type' => ['nullable', 'array'],
            /**
             * Menu type ID (individual item). Must exist in menu_type table.
             *
             * @var int|null $menu_type.*
             * @example 1
             */
            'menu_type.*' => ['nullable', 'integer', 'exists:menu_type,id'],
            /**
             * Comma-separated list of extra product IDs for restaurant module.
             *
             * @var string|null $extras
             * @example 10,11,12
             */
            'extras' => ['nullable', 'string'],
            /**
             * Daily sales objective. Must be numeric and non-negative.
             *
             * @var float|null $daily_sale_objective
             * @example 1000.00
             */
            'daily_sale_objective' => ['nullable', 'numeric', 'min:0'],
            
            /**
             * Production cost. Must be numeric and non-negative.
             *
             * @var float|null $production_cost
             * @example 40.00
             */
            'production_cost' => ['nullable', 'numeric', 'min:0'],
            
            /**
             * WooCommerce product ID for external system sync.
             *
             * @var int|null $woocommerce_product_id
             * @example 12345
             */
            'woocommerce_product_id' => ['nullable', 'integer'],
            /**
             * WooCommerce media ID for external system sync.
             *
             * @var int|null $woocommerce_media_id
             * @example 67890
             */
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
     * Normalizes FormData values to match Product model's $casts.
     * FormData may send booleans as "true"/"false"/"1"/"0", integers/floats as strings, etc.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $mergeData = array_merge(
            $this->normalizeBooleans(),
            $this->normalizeIntegers(),
            $this->normalizeFloats()
        );

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Normalize boolean fields from FormData.
     *
     * FormData sends booleans as strings ("true"/"false"/"1"/"0").
     * Converts them to actual boolean values matching Product model's $casts.
     *
     * @return array<string, bool>
     */
    protected function normalizeBooleans(): array
    {
        $booleanFields = [
            'is_variant',
            'is_batch',
            'is_imei',
            'is_diff_price',
            'is_active',
            'featured',
            'promotion',
            'is_online',
            'in_stock',
            'is_addon',
            'is_recipe',
            'is_embeded',
            'is_sync_disable',
            'is_initial_stock',
            'track_inventory',
        ];

        $normalized = [];

        foreach ($booleanFields as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                $normalized[$field] = false;
                continue;
            }

            $normalized[$field] = filter_var(
                $value,
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ?? false;
        }

        return $normalized;
    }

    /**
     * Normalize integer fields from FormData.
     *
     * FormData may send integers as strings.
     * Converts them to actual integer values.
     *
     * @return array<string, int|null>
     */
    protected function normalizeIntegers(): array
    {
        $integerFields = [
            'brand_id',
            'category_id',
            'unit_id',
            'purchase_unit_id',
            'sale_unit_id',
            'tax_id',
            'tax_method',
            'kitchen_id',
            'woocommerce_product_id',
            'woocommerce_media_id',
            'warranty',
            'guarantee',
        ];

        $normalized = [];

        foreach ($integerFields as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$field] = is_numeric($value) ? (int)$value : null;
        }

        return $normalized;
    }

    /**
     * Normalize float fields from FormData.
     *
     * FormData may send floats as strings.
     * Converts them to actual float values.
     *
     * @return array<string, float|null>
     */
    protected function normalizeFloats(): array
    {
        $floatFields = [
            'cost',
            'profit_margin',
            'price',
            'wholesale_price',
            'qty',
            'alert_quantity',
            'daily_sale_objective',
            'promotion_price',
            'production_cost',
        ];

        $normalized = [];

        foreach ($floatFields as $field) {
            if (!$this->has($field)) {
                continue;
            }

            $value = $this->input($field);

            if ($value === null || $value === '') {
                continue;
            }

            $normalized[$field] = is_numeric($value) ? (float)$value : null;
        }

        return $normalized;
    }
}

