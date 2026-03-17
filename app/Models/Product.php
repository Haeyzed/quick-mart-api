<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProductTypeEnum;
use App\Enums\TaxMethodEnum;
use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Product
 *
 * Represents a product in the inventory system with support for variants, batches, and multiple pricing.
 * Handles the underlying data structure, relationships, and specific query scopes for product entities.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property ProductTypeEnum $type
 * @property string|null $slug
 * @property string $barcode_symbology
 * @property int|null $brand_id
 * @property int $category_id
 * @property int $unit_id
 * @property int|null $purchase_unit_id
 * @property int|null $sale_unit_id
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
 * @property Carbon|null $starting_date
 * @property Carbon|null $last_date
 * @property int|null $tax_id
 * @property TaxMethodEnum|null $tax_method
 * @property array|null $image_paths
 * @property array|null $image_urls
 * @property string|null $file_path
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
 * @property array|null $specification
 * @property string|null $related_products
 * @property bool|null $is_addon
 * @property string|null $extras
 * @property string|null $menu_type
 * @property array|null $variant_option
 * @property array|null $variant_value
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
 * @property string|null $wastage_percent
 * @property string|null $combo_unit_id
 * @property float|null $production_cost
 * @property bool|null $is_recipe
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read Category|null $category
 * @property-read Brand|null $brand
 * @property-read Tax|null $tax
 * @property-read Unit|null $unit
 * @property-read Unit|null $purchaseUnit
 * @property-read Unit|null $saleUnit
 * @property-read Unit|null $comboUnit
 * @property-read Kitchen|null $kitchen
 * @property-read Collection<int, Variant> $variants
 * @property-read Collection<int, Warehouse> $warehouses
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Returns> $saleReturns
 * @property-read Collection<int, ReturnPurchase> $purchaseReturns
 * @property-read Collection<int, Adjustment> $adjustments
 * @property-read Collection<int, Transfer> $transfers
 * @property-read Collection<int, ProductBatch> $batches
 * @property-read Collection<int, ProductVariant> $productVariants
 * @property-read Collection<int, ProductWarehouse> $productWarehouses
 *
 * @method static Builder|Product active()
 * @method static Builder|Product activeStandard()
 * @method static Builder|Product activeFeatured()
 * @method static Builder|Product featured()
 * @method static Builder|Product online()
 * @method static Builder|Product filter(array $filters)
 *
 * @mixin \Eloquent
 */
class Product extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

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
        'image_paths',
        'image_urls',
        'file_path',
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

    protected function casts(): array
    {
        return [
            'type' => ProductTypeEnum::class,
            'tax_method' => TaxMethodEnum::class,
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
            'wastage_percent' => 'string',
            'combo_unit_id' => 'string',
            'production_cost' => 'float',
            'is_recipe' => 'boolean',
            'image_paths' => 'array',
            'image_urls' => 'array',
            'file_url' => 'string',
            'variant_option' => 'array',
            'variant_value' => 'array',
        ];
    }

    /**
     * Scope a query to apply dynamic filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->whereIn('is_active', (array) $filters['is_active'])
            )
            ->when(
                isset($filters['featured']),
                fn (Builder $q) => $q->whereIn('featured', (array) $filters['featured'])
            )
            ->when(
                isset($filters['type']),
                fn (Builder $q) => $q->whereIn('type', (array) $filters['type'])
            )
            ->when(
                isset($filters['brand_id']),
                fn (Builder $q) => $q->whereIn('brand_id', (array) $filters['brand_id'])
            )
            ->when(
                isset($filters['category_id']),
                fn (Builder $q) => $q->whereIn('category_id', (array) $filters['category_id'])
            )
            ->when(
                isset($filters['unit_id']),
                fn (Builder $q) => $q->whereIn('unit_id', (array) $filters['unit_id'])
            )
            ->when(
                isset($filters['is_imei']),
                fn (Builder $q) => $q->where('is_imei', filter_var($filters['is_imei'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when(
                isset($filters['is_variant']),
                fn (Builder $q) => $q->where('is_variant', filter_var($filters['is_variant'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when(
                isset($filters['is_recipe']),
                fn (Builder $q) => $q->where('is_recipe', filter_var($filters['is_recipe'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when(
                isset($filters['stock_filter']) && $filters['stock_filter'] !== 'all',
                function (Builder $q) use ($filters) {
                    if ($filters['stock_filter'] === 'with_stock') {
                        $q->whereHas('productWarehouses', fn($subQ) => $subQ->havingRaw('SUM(qty) > 0'));
                    } elseif ($filters['stock_filter'] === 'without_stock') {
                        $q->whereDoesntHave('productWarehouses', fn($subQ) => $subQ->havingRaw('SUM(qty) > 0'));
                    }
                }
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(function (Builder $subQ) use ($term) {
                        $subQ->where('products.name', 'like', $term)
                            ->orWhere('products.code', 'like', $term)
                            ->orWhereHas('brand', fn($b) => $b->where('name', 'like', $term))
                            ->orWhereHas('category', fn($c) => $c->where('name', 'like', $term))
                            ->orWhereHas('productVariants', fn($pv) => $pv->where('item_code', 'like', $term))
                            ->orWhereHas('purchases', fn($pp) => $pp->where('imei_number', 'like', $term));

                        // Also account for custom fields dynamically if needed
                        $customFields = CustomField::where('belongs_to', 'product')->pluck('name');
                        foreach ($customFields as $field) {
                            $safeField = str_replace(' ', '_', strtolower($field));
                            if (Schema::hasColumn('products', $safeField)) {
                                $subQ->orWhere("products.{$safeField}", 'like', $term);
                            }
                        }
                    });
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Product $product) {
            $product->generateSlugIfNeeded();
        });
    }

    protected function generateSlugIfNeeded(): void
    {
        $generalSetting = GeneralSetting::latest()->first();
        $modules = explode(',', $generalSetting->modules ?? '');
        $hasEcommerce = in_array('ecommerce', $modules);
        $hasRestaurant = in_array('restaurant', $modules);

        if (! $hasEcommerce && ! $hasRestaurant) {
            return;
        }

        if ($this->name && ! $this->slug) {
            $this->slug = Str::slug($this->name, '-');
        }

        if ($this->slug) {
            $this->slug = preg_replace('/[^A-Za-z0-9\-]/', '', $this->slug);
            $this->slug = str_replace('\/', '/', $this->slug);
        }
    }

    // ------------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function tax(): BelongsTo
    {
        return $this->belongsTo(Tax::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    public function saleUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    public function comboUnit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'combo_unit_id');
    }

    public function kitchen(): BelongsTo
    {
        return $this->belongsTo(Kitchen::class); // Make sure the Kitchen model exists
    }

    public function variants(): BelongsToMany
    {
        return $this->belongsToMany(Variant::class, 'product_variants')
            ->withPivot('id', 'item_code', 'additional_cost', 'additional_price', 'qty')
            ->withTimestamps();
    }

    public function warehouses(): BelongsToMany
    {
        return $this->belongsToMany(Warehouse::class, 'product_warehouse')
            ->withPivot('qty', 'price', 'product_batch_id', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    public function purchases(): BelongsToMany
    {
        return $this->belongsToMany(Purchase::class, 'product_purchases')
            ->withPivot('qty', 'tax', 'tax_rate', 'discount', 'total', 'product_batch_id', 'variant_id', 'net_unit_cost', 'net_unit_price')
            ->withTimestamps();
    }

    public function sales(): BelongsToMany
    {
        return $this->belongsToMany(Sale::class, 'product_sales')
            ->withPivot('qty', 'product_batch_id', 'return_qty', 'net_unit_price', 'tax', 'discount', 'tax_rate', 'total', 'is_delivered', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    public function saleReturns(): BelongsToMany
    {
        return $this->belongsToMany(Returns::class, 'product_returns', 'product_id', 'return_id')
            ->withPivot('qty', 'sale_unit_id', 'net_unit_price', 'discount', 'tax_rate', 'tax', 'total', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    public function purchaseReturns(): BelongsToMany
    {
        return $this->belongsToMany(ReturnPurchase::class, 'purchase_product_return', 'product_id', 'return_id')
            ->withPivot('qty', 'purchase_unit_id', 'net_unit_cost', 'discount', 'tax_rate', 'tax', 'total', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    public function adjustments(): BelongsToMany
    {
        return $this->belongsToMany(Adjustment::class, 'product_adjustments')
            ->withPivot('variant_id', 'unit_cost', 'qty', 'action')
            ->withTimestamps();
    }

    public function transfers(): BelongsToMany
    {
        return $this->belongsToMany(Transfer::class, 'product_transfer')
            ->withPivot('product_batch_id', 'variant_id', 'imei_number', 'qty', 'purchase_unit_id', 'net_unit_cost', 'tax_rate', 'tax', 'total')
            ->withTimestamps();
    }

    public function batches(): HasMany
    {
        return $this->hasMany(ProductBatch::class);
    }

    public function productVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function productWarehouses(): HasMany
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    // ------------------------------------------------------------------------
    // Helpers & Scopes
    // ------------------------------------------------------------------------

    public function getEffectivePrice(): float
    {
        return $this->isOnPromotion() && $this->promotion_price
            ? (float) $this->promotion_price
            : $this->price;
    }

    public function isOnPromotion(): bool
    {
        if (! $this->promotion) {
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

    public function isLowStock(): bool
    {
        if (! $this->alert_quantity || ! $this->track_inventory) {
            return false;
        }

        return $this->qty <= $this->alert_quantity;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeActiveStandard(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('type', ProductTypeEnum::STANDARD->value);
    }

    public function scopeActiveFeatured(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where('featured', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('is_online', true);
    }
}
