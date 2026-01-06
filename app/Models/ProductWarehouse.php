<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ProductWarehouse Model (Pivot)
 *
 * Represents the relationship between products and warehouses with stock quantity information.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property int $warehouse_id
 * @property float $qty
 * @property float|null $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Product $product
 * @property-read Warehouse $warehouse
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 *
 * @method static Builder|ProductWarehouse findProductWithVariant(int $productId, int $variantId, int $warehouseId)
 * @method static Builder|ProductWarehouse findProductWithoutVariant(int $productId, int $warehouseId)
 */
class ProductWarehouse extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_warehouse';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'product_batch_id',
        'variant_id',
        'imei_number',
        'warehouse_id',
        'qty',
        'price',
    ];

    /**
     * Get the product for this warehouse stock.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the warehouse for this stock.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the batch for this warehouse stock.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this warehouse stock.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Scope a query to find a product with variant in a specific warehouse.
     *
     * @param Builder $query
     * @param int $productId
     * @param int $variantId
     * @param int $warehouseId
     * @return Builder
     */
    public function scopeFindProductWithVariant(Builder $query, int $productId, int $variantId, int $warehouseId): Builder
    {
        return $query->where('product_id', $productId)
            ->where('variant_id', $variantId)
            ->where('warehouse_id', $warehouseId);
    }

    /**
     * Scope a query to find a product without variant in a specific warehouse.
     *
     * @param Builder $query
     * @param int $productId
     * @param int $warehouseId
     * @return Builder
     */
    public function scopeFindProductWithoutVariant(Builder $query, int $productId, int $warehouseId): Builder
    {
        return $query->where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->whereNull('variant_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
            'warehouse_id' => 'integer',
            'qty' => 'float',
            'price' => 'float',
        ];
    }
}

