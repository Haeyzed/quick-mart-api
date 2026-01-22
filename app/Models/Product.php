<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Product Model
 *
 * Represents a product in the inventory system with support for variants, batches, and multiple pricing.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property string|null $slug
 * @property string $barcode_symbology
 * @property int|null $brand_id
 * @property int $category_id
 * @property int $unit_id
 * @property int $purchase_unit_id
 * @property int $sale_unit_id
 * @property float $cost
 * @property float|null $profit_margin
 * @property string|null $profit_margin_type
 * @property float $price
 * @property float|null $wholesale_price
 * @property float|null $qty
 * @property float|null $alert_quantity
 * @property float|null $daily_sale_objective
 * @property bool|null $promotion
 * @property float|null $promotion_price
 * @property \Illuminate\Support\Carbon|null $starting_date
 * @property \Illuminate\Support\Carbon|null $last_date
 * @property int|null $tax_id
 * @property int|null $tax_method
 * @property array|null $image
 * @property array|null $image_url
 * @property string|null $file
 * @property string|null $file_url
 * @property bool|null $is_embeded
 * @property bool $is_batch
 * @property bool $is_variant
 * @property bool $is_diff_price
 * @property bool $is_imei
 * @property bool|null $featured
 * @property string|null $product_list
 * @property string|null $variant_list
 * @property string|null $qty_list
 * @property string|null $price_list
 * @property array|null $product_details
 * @property string|null $short_description
 * @property string|null $specification
 * @property string|null $related_products
 * @property bool|null $is_addon
 * @property string|null $extras
 * @property string|null $menu_type
 * @property string|null $variant_option
 * @property string|null $variant_value
 * @property bool $is_active
 * @property bool|null $is_online
 * @property int|null $kitchen_id
 * @property bool|null $in_stock
 * @property bool $track_inventory
 * @property bool|null $is_sync_disable
 * @property int|null $woocommerce_product_id
 * @property int|null $woocommerce_media_id
 * @property string|null $tags
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int|null $warranty
 * @property int|null $guarantee
 * @property string|null $warranty_type
 * @property string|null $guarantee_type
 * @property float|null $wastage_percent
 * @property int|null $combo_unit_id
 * @property float|null $production_cost
 * @property bool|null $is_recipe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @property-read Category $category
 * @property-read Brand|null $brand
 * @property-read Tax|null $tax
 * @property-read Unit $unit
 * @property-read Unit $purchaseUnit
 * @property-read Unit $saleUnit
 * @property-read Collection<int, Variant> $variants
 * @property-read Collection<int, Warehouse> $warehouses
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, ProductBatch> $batches
 * @property-read Collection<int, ProductVariant> $productVariants
 * @property-read Collection<int, ProductWarehouse> $productWarehouses
 *
 * @method static Builder|Product active()
 * @method static Builder|Product activeStandard()
 * @method static Builder|Product activeFeatured()
 * @method static Builder|Product featured()
 * @method static Builder|Product online()
 */
class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'slug',
        'barcode_symbology',
        'brand_id',
        'category_id',
        'unit_id',
        'purchase_unit_id',
        'sale_unit_id',
        'cost',
        'profit_margin',
        'profit_margin_type',
        'price',
        'wholesale_price',
        'qty',
        'alert_quantity',
        'daily_sale_objective',
        'promotion',
        'promotion_price',
        'starting_date',
        'last_date',
        'tax_id',
        'tax_method',
        'image',
        'image_url',
        'file',
        'file_url',
        'is_embeded',
        'is_batch',
        'is_variant',
        'is_diff_price',
        'is_imei',
        'featured',
        'product_list',
        'variant_list',
        'qty_list',
        'price_list',
        'product_details',
        'short_description',
        'specification',
        'related_products',
        'is_addon',
        'extras',
        'menu_type',
        'variant_option',
        'variant_value',
        'is_active',
        'is_online',
        'kitchen_id',
        'in_stock',
        'track_inventory',
        'is_sync_disable',
        'woocommerce_product_id',
        'woocommerce_media_id',
        'tags',
        'meta_title',
        'meta_description',
        'warranty',
        'guarantee',
        'warranty_type',
        'guarantee_type',
        'wastage_percent',
        'combo_unit_id',
        'production_cost',
        'is_recipe',
    ];

    /**
     * Get the category for this product.
     *
     * @return BelongsTo<Category, self>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the brand for this product.
     *
     * @return BelongsTo<Brand, self>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the tax for this product.
     *
     * @return BelongsTo<Tax, self>
     */
    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    /**
     * Get the unit for this product.
     *
     * @return BelongsTo<Unit, self>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the purchase unit for this product.
     *
     * @return BelongsTo<Unit, self>
     */
    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    /**
     * Get the sale unit for this product.
     *
     * @return BelongsTo<Unit, self>
     */
    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    /**
     * Get the variants for this product.
     *
     * @return BelongsToMany<Variant>
     */
    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Variant::class, 'product_variants')
            ->withPivot('id', 'item_code', 'additional_cost', 'additional_price', 'qty')
            ->withTimestamps();
    }

    /**
     * Get the warehouses for this product.
     *
     * @return BelongsToMany<Warehouse>
     */
    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('qty', 'price', 'product_batch_id', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    /**
     * Get the purchases for this product.
     *
     * @return BelongsToMany<Purchase>
     */
    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Purchase::class, 'product_purchases')
            ->withPivot('qty', 'tax', 'tax_rate', 'discount', 'total', 'product_batch_id', 'variant_id', 'net_unit_cost', 'net_unit_price')
            ->withTimestamps();
    }

    /**
     * Get the sales for this product.
     *
     * @return BelongsToMany<Sale>
     */
    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'product_sales')
            ->withPivot('qty', 'product_batch_id', 'return_qty', 'net_unit_price', 'tax', 'discount', 'tax_rate', 'total', 'is_delivered', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    /**
     * Get the batches for this product.
     *
     * @return HasMany<ProductBatch>
     */
    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    /**
     * Get the product variants.
     *
     * @return HasMany<ProductVariant>
     */
    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * Get the product warehouse relationships.
     *
     * @return HasMany<ProductWarehouse>
     */
    public function productWarehouses(): HasMany
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    /**
     * Get the effective price (promotion price if on promotion, otherwise regular price).
     *
     * @return float
     */
    public function getEffectivePrice(): float
    {
        return $this->isOnPromotion() && $this->promotion_price
            ? (float)$this->promotion_price
            : $this->price;
    }

    /**
     * Check if the product is on promotion.
     *
     * @return bool
     */
    public function isOnPromotion(): bool
    {
        if (!$this->promotion) {
            return false;
        }

        $now = now();
        $startDate = $this->starting_date ? Carbon::parse($this->starting_date) : null;
        $endDate = $this->last_date ? Carbon::parse($this->last_date) : null;

        if ($startDate && $now->lt($startDate)) {
            return false;
        }

        if ($endDate && $now->gt($endDate)) {
            return false;
        }

        return true;
    }

    /**
     * Check if product quantity is below alert threshold.
     *
     * @return bool
     */
    public function isLowStock(): bool
    {
        if (!$this->alert_quantity || !$this->track_inventory) {
            return false;
        }

        return $this->qty <= $this->alert_quantity;
    }

    /**
     * Scope a query to only include active products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include active standard products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActiveStandard(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('type', 'standard');
    }

    /**
     * Scope a query to only include active featured products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActiveFeatured(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('featured', true);
    }

    /**
     * Scope a query to only include featured products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include online products.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('is_online', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'unit_id' => 'integer',
            'purchase_unit_id' => 'integer',
            'sale_unit_id' => 'integer',
            'cost' => 'float',
            'profit_margin' => 'float',
            'price' => 'float',
            'wholesale_price' => 'float',
            'qty' => 'float',
            'alert_quantity' => 'float',
            'daily_sale_objective' => 'float',
            'promotion' => 'boolean',
            'promotion_price' => 'float',
            'starting_date' => 'date',
            'last_date' => 'date',
            'tax_id' => 'integer',
            'tax_method' => 'integer',
            'is_embeded' => 'boolean',
            'is_batch' => 'boolean',
            'is_variant' => 'boolean',
            'is_diff_price' => 'boolean',
            'is_imei' => 'boolean',
            'featured' => 'boolean',
            'is_addon' => 'boolean',
            'is_active' => 'boolean',
            'is_online' => 'boolean',
            'kitchen_id' => 'integer',
            'in_stock' => 'boolean',
            'track_inventory' => 'boolean',
            'is_sync_disable' => 'boolean',
            'woocommerce_product_id' => 'integer',
            'woocommerce_media_id' => 'integer',
            'warranty' => 'integer',
            'guarantee' => 'integer',
            'wastage_percent' => 'float',
            'combo_unit_id' => 'integer',
            'production_cost' => 'float',
            'is_recipe' => 'boolean',
            'image' => 'array',
            'image_url' => 'array',
            'file_url' => 'string',
            'product_details' => 'array',
        ];
    }

    /**
     * Boot the model and set up event listeners for slug generation.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            $product->generateSlugIfNeeded();
        });
    }

    /**
     * Generate and normalize slug if ecommerce/restaurant module is enabled and slug is missing.
     *
     * @return void
     */
    protected function generateSlugIfNeeded(): void
    {
        // Check if ecommerce or restaurant module is enabled
        $generalSetting = \App\Models\GeneralSetting::latest()->first();
        $modules = explode(',', $generalSetting->modules ?? '');
        $hasEcommerce = in_array('ecommerce', $modules);
        $hasRestaurant = in_array('restaurant', $modules);

        if (!$hasEcommerce && !$hasRestaurant) {
            return;
        }

        // Generate slug if name exists and slug is missing
        if ($this->name && !$this->slug) {
            $this->slug = Str::slug($this->name, '-');
        }

        // Normalize slug if it exists
        if ($this->slug) {
            $this->slug = preg_replace('/[^A-Za-z0-9\-]/', '', $this->slug);
            $this->slug = str_replace('\/', '/', $this->slug);
        }
    }
}

