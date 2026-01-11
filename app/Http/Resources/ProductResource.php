<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ProductWarehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

/**
 * ProductResource
 *
 * Transforms a Product model instance into a JSON response with full documentation
 * for each field to ensure API documentation clarity.
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // Get warehouse quantity if warehouse_id is provided
        $warehouseId = $request->input('warehouse_id');
        $qty = $this->getQuantity($warehouseId);

        // Format images
        $images = $this->formatImages();

        return [
            /**
             * The unique identifier for the product.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Product name.
             *
             * @var string $name
             * @example Laptop Computer
             */
            'name' => $this->name,

            /**
             * Product code/SKU.
             *
             * @var string $code
             * @example LAP001
             */
            'code' => $this->code,

            /**
             * Product type: standard, combo, digital, or service.
             *
             * @var string $type
             * @example standard
             */
            'type' => $this->type,

            /**
             * URL-friendly slug for the product.
             *
             * @var string|null $slug
             * @example laptop-computer
             */
            'slug' => $this->slug,

            /**
             * Barcode symbology type.
             *
             * @var string $barcode_symbology
             * @example C128
             */
            'barcode_symbology' => $this->barcode_symbology,

            /**
             * Brand information.
             *
             * @var array|null $brand
             */
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'title' => $this->brand->title,
                ];
            }),

            /**
             * Brand ID.
             *
             * @var int|null $brand_id
             * @example 1
             */
            'brand_id' => $this->brand_id,

            /**
             * Category information.
             *
             * @var array|null $category
             */
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),

            /**
             * Category ID.
             *
             * @var int $category_id
             * @example 1
             */
            'category_id' => $this->category_id,

            /**
             * Unit information.
             *
             * @var array|null $unit
             */
            'unit' => $this->whenLoaded('unit', function () {
                return [
                    'id' => $this->unit->id,
                    'unit_name' => $this->unit->unit_name,
                    'unit_code' => $this->unit->unit_code,
                ];
            }),

            /**
             * Unit ID.
             *
             * @var int $unit_id
             * @example 1
             */
            'unit_id' => $this->unit_id,

            /**
             * Purchase unit ID.
             *
             * @var int|null $purchase_unit_id
             * @example 1
             */
            'purchase_unit_id' => $this->purchase_unit_id,

            /**
             * Sale unit ID.
             *
             * @var int|null $sale_unit_id
             * @example 1
             */
            'sale_unit_id' => $this->sale_unit_id,

            /**
             * Product cost.
             *
             * @var float $cost
             * @example 100.00
             */
            'cost' => (float)$this->cost,

            /**
             * Profit margin.
             *
             * @var float|null $profit_margin
             * @example 25.00
             */
            'profit_margin' => $this->profit_margin ? (float)$this->profit_margin : null,

            /**
             * Profit margin type: percentage or fixed.
             *
             * @var string|null $profit_margin_type
             * @example percentage
             */
            'profit_margin_type' => $this->profit_margin_type,

            /**
             * Product price.
             *
             * @var float $price
             * @example 125.00
             */
            'price' => (float)$this->price,

            /**
             * Wholesale price.
             *
             * @var float|null $wholesale_price
             * @example 110.00
             */
            'wholesale_price' => $this->wholesale_price ? (float)$this->wholesale_price : null,

            /**
             * Product quantity.
             *
             * @var float|null $qty
             * @example 50.00
             */
            'qty' => $qty,

            /**
             * Alert quantity threshold.
             *
             * @var float|null $alert_quantity
             * @example 10.00
             */
            'alert_quantity' => $this->alert_quantity ? (float)$this->alert_quantity : null,

            /**
             * Daily sale objective.
             *
             * @var float|null $daily_sale_objective
             * @example 5.00
             */
            'daily_sale_objective' => $this->daily_sale_objective ? (float)$this->daily_sale_objective : null,

            /**
             * Whether product is on promotion.
             *
             * @var bool|null $promotion
             * @example false
             */
            'promotion' => $this->promotion ? true : false,

            /**
             * Promotion price.
             *
             * @var float|null $promotion_price
             * @example 100.00
             */
            'promotion_price' => $this->promotion_price ? (float)$this->promotion_price : null,

            /**
             * Promotion start date.
             *
             * @var string|null $starting_date
             * @example 2024-01-01
             */
            'starting_date' => $this->starting_date?->format('Y-m-d'),

            /**
             * Promotion end date.
             *
             * @var string|null $last_date
             * @example 2024-12-31
             */
            'last_date' => $this->last_date?->format('Y-m-d'),

            /**
             * Tax information.
             *
             * @var array|null $tax
             */
            'tax' => $this->whenLoaded('tax', function () {
                return [
                    'id' => $this->tax->id,
                    'name' => $this->tax->name,
                    'rate' => $this->tax->rate,
                ];
            }),

            /**
             * Tax ID.
             *
             * @var int|null $tax_id
             * @example 1
             */
            'tax_id' => $this->tax_id,

            /**
             * Tax method: 0 = Inclusive, 1 = Exclusive.
             *
             * @var int|null $tax_method
             * @example 1
             */
            'tax_method' => $this->tax_method,

            /**
             * Product images (array of image names/paths).
             *
             * @var array|null $image
             * @example ["image1.jpg", "image2.jpg"]
             */
            'image' => $images['names'],

            /**
             * Product image URLs (array of full URLs).
             *
             * @var array|null $image_url
             * @example ["https://example.com/images/product/image1.jpg"]
             */
            'image_url' => $images['urls'],

            /**
             * Digital product file name.
             *
             * @var string|null $file
             * @example product-file.pdf
             */
            'file' => $this->file,

            /**
             * Digital product file URL.
             *
             * @var string|null $file_url
             * @example https://example.com/storage/product/files/product-file.pdf
             */
            'file_url' => $this->file_url,

            /**
             * Whether images are embedded.
             *
             * @var bool|null $is_embeded
             * @example false
             */
            'is_embeded' => $this->is_embeded ? true : false,

            /**
             * Whether product uses batch tracking.
             *
             * @var bool $is_batch
             * @example false
             */
            'is_batch' => $this->is_batch ? true : false,

            /**
             * Whether product has variants.
             *
             * @var bool $is_variant
             * @example false
             */
            'is_variant' => $this->is_variant ? true : false,

            /**
             * Whether product has different prices per warehouse.
             *
             * @var bool $is_diffPrice
             * @example false
             */
            'is_diffPrice' => $this->is_diffPrice ? true : false,

            /**
             * Whether product uses IMEI tracking.
             *
             * @var bool $is_imei
             * @example false
             */
            'is_imei' => $this->is_imei ? true : false,

            /**
             * Whether product is featured.
             *
             * @var bool|null $featured
             * @example false
             */
            'featured' => $this->featured ? true : false,

            /**
             * Combo product list (comma-separated product IDs).
             *
             * @var string|null $product_list
             * @example "1,2,3"
             */
            'product_list' => $this->product_list,

            /**
             * Variant list (comma-separated variant IDs).
             *
             * @var string|null $variant_list
             * @example "1,2,3"
             */
            'variant_list' => $this->variant_list,

            /**
             * Quantity list for combo products.
             *
             * @var string|null $qty_list
             * @example "1,2,1"
             */
            'qty_list' => $this->qty_list,

            /**
             * Price list for combo products.
             *
             * @var string|null $price_list
             * @example "10,20,15"
             */
            'price_list' => $this->price_list,

            /**
             * Product details/description.
             *
             * @var string|null $product_details
             * @example "High-quality laptop with latest specifications"
             */
            'product_details' => $this->product_details,

            /**
             * Short description.
             *
             * @var string|null $short_description
             * @example "Premium laptop computer"
             */
            'short_description' => $this->short_description,

            /**
             * Product specifications.
             *
             * @var string|null $specification
             * @example "Intel i7, 16GB RAM, 512GB SSD"
             */
            'specification' => $this->specification,

            /**
             * Related products (comma-separated product IDs).
             *
             * @var string|null $related_products
             * @example "4,5,6"
             */
            'related_products' => $this->related_products,

            /**
             * Whether product is an addon.
             *
             * @var bool|null $is_addon
             * @example false
             */
            'is_addon' => $this->is_addon ? true : false,

            /**
             * Extras (comma-separated product IDs for restaurant module).
             *
             * @var string|null $extras
             * @example "7,8,9"
             */
            'extras' => $this->extras,

            /**
             * Menu type (comma-separated IDs for restaurant module).
             *
             * @var string|null $menu_type
             * @example "1,2"
             */
            'menu_type' => $this->menu_type,

            /**
             * Variant options (JSON string).
             *
             * @var string|null $variant_option
             * @example '["Color","Size"]'
             */
            'variant_option' => $this->variant_option ? json_decode($this->variant_option) : null,

            /**
             * Variant values (JSON string).
             *
             * @var string|null $variant_value
             * @example '["Red,Blue","S,M,L"]'
             */
            'variant_value' => $this->variant_value ? json_decode($this->variant_value) : null,

            /**
             * Whether product is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Whether product is available online.
             *
             * @var bool|null $is_online
             * @example true
             */
            'is_online' => $this->is_online ? true : false,

            /**
             * Kitchen ID (for restaurant module).
             *
             * @var int|null $kitchen_id
             * @example 1
             */
            'kitchen_id' => $this->kitchen_id,

            /**
             * Whether product is in stock.
             *
             * @var bool|null $in_stock
             * @example true
             */
            'in_stock' => $this->in_stock ? true : false,

            /**
             * Whether inventory tracking is enabled.
             *
             * @var bool $track_inventory
             * @example true
             */
            'track_inventory' => (bool)$this->track_inventory,

            /**
             * Whether sync is disabled.
             *
             * @var bool|null $is_sync_disable
             * @example false
             */
            'is_sync_disable' => $this->is_sync_disable ? true : false,

            /**
             * WooCommerce product ID.
             *
             * @var int|null $woocommerce_product_id
             * @example 123
             */
            'woocommerce_product_id' => $this->woocommerce_product_id,

            /**
             * WooCommerce media ID.
             *
             * @var int|null $woocommerce_media_id
             * @example 456
             */
            'woocommerce_media_id' => $this->woocommerce_media_id,

            /**
             * Product tags.
             *
             * @var string|null $tags
             * @example "electronics,laptop,computer"
             */
            'tags' => $this->tags,

            /**
             * Meta title for SEO.
             *
             * @var string|null $meta_title
             * @example "Best Laptop Computer 2024"
             */
            'meta_title' => $this->meta_title,

            /**
             * Meta description for SEO.
             *
             * @var string|null $meta_description
             * @example "Shop the best laptops online"
             */
            'meta_description' => $this->meta_description,

            /**
             * Warranty period.
             *
             * @var int|null $warranty
             * @example 12
             */
            'warranty' => $this->warranty,

            /**
             * Guarantee period.
             *
             * @var int|null $guarantee
             * @example 6
             */
            'guarantee' => $this->guarantee,

            /**
             * Warranty type (e.g., months, years).
             *
             * @var string|null $warranty_type
             * @example months
             */
            'warranty_type' => $this->warranty_type,

            /**
             * Guarantee type (e.g., months, years).
             *
             * @var string|null $guarantee_type
             * @example months
             */
            'guarantee_type' => $this->guarantee_type,

            /**
             * Wastage percent for combo products.
             *
             * @var float|null $wastage_percent
             * @example 5.00
             */
            'wastage_percent' => $this->wastage_percent ? (float)$this->wastage_percent : null,

            /**
             * Combo unit ID.
             *
             * @var int|null $combo_unit_id
             * @example 1
             */
            'combo_unit_id' => $this->combo_unit_id,

            /**
             * Production cost.
             *
             * @var float|null $production_cost
             * @example 90.00
             */
            'production_cost' => $this->production_cost ? (float)$this->production_cost : null,

            /**
             * Whether product is a recipe.
             *
             * @var bool|null $is_recipe
             * @example false
             */
            'is_recipe' => $this->is_recipe ? true : false,

            /**
             * Product variants.
             *
             * @var array|null $variants
             */
            'variants' => $this->whenLoaded('productVariants', function () {
                return $this->productVariants->map(function ($variant) {
                    return [
                        'id' => $variant->id,
                        'variant_id' => $variant->variant_id,
                        'variant_name' => $variant->variant->name ?? null,
                        'item_code' => $variant->item_code,
                        'additional_cost' => (float)$variant->additional_cost,
                        'additional_price' => (float)$variant->additional_price,
                        'qty' => (float)$variant->qty,
                        'position' => $variant->position,
                    ];
                });
            }),

            /**
             * ISO 8601 formatted creation timestamp.
             *
             * @var string|null $created_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * ISO 8601 formatted last update timestamp.
             *
             * @var string|null $updated_at
             * @example 2024-01-15T15:45:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Get product quantity based on warehouse.
     *
     * @param int|null $warehouseId
     * @return float|null
     */
    private function getQuantity(?int $warehouseId): ?float
    {
        if ($this->type == 'standard') {
            if ($warehouseId && $warehouseId > 0) {
                $qty = ProductWarehouse::where('product_id', $this->id)
                    ->where('warehouse_id', $warehouseId)
                    ->sum('qty');
                return $qty ? (float)$qty : 0.0;
            } else {
                $qty = ProductWarehouse::where('product_id', $this->id)
                    ->sum('qty');
                return $qty ? (float)$qty : 0.0;
            }
        }

        return $this->qty ? (float)$this->qty : null;
    }

    /**
     * Format product images.
     *
     * @return array{names: array<string>, urls: array<string>}
     */
    private function formatImages(): array
    {
        $names = [];
        $urls = [];

        if ($this->image) {
            if (is_array($this->image)) {
                $imageArray = $this->image;
            } else {
                $imageArray = explode(',', $this->image);
            }

            foreach ($imageArray as $image) {
                $image = trim($image);
                if ($image && $image !== 'zummXD2dvAtI.png') {
                    $names[] = $image;
                    $urls[] = URL::asset('images/product/' . $image);
                }
            }
        }

        // If no images, add default
        if (empty($names)) {
            $names[] = 'zummXD2dvAtI.png';
            $urls[] = URL::asset('images/zummXD2dvAtI.png');
        }

        return [
            'names' => $names,
            'urls' => $urls,
        ];
    }
}

