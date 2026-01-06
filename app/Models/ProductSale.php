<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ProductSale Model (Pivot)
 *
 * Represents the relationship between products and sales with additional sale-specific data.
 *
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property float $return_qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property bool|null $is_packing
 * @property bool $is_delivered
 * @property int|null $topping_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Sale $sale
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 *
 * @method static Builder|ProductSale delivered()
 * @method static Builder|ProductSale notDelivered()
 */
class ProductSale extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_sales';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sale_id',
        'product_id',
        'product_batch_id',
        'variant_id',
        'imei_number',
        'qty',
        'return_qty',
        'sale_unit_id',
        'net_unit_price',
        'discount',
        'tax_rate',
        'tax',
        'total',
        'is_packing',
        'is_delivered',
        'topping_id',
    ];

    /**
     * Get the sale that owns this product sale.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the product for this sale.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this product sale.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this product sale.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Calculate the net quantity (qty - return_qty).
     *
     * @return float
     */
    public function getNetQty(): float
    {
        return $this->qty - $this->return_qty;
    }

    /**
     * Scope a query to only include delivered items.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('is_delivered', true);
    }

    /**
     * Scope a query to only include not delivered items.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotDelivered(Builder $query): Builder
    {
        return $query->where('is_delivered', false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_id' => 'integer',
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
            'qty' => 'float',
            'return_qty' => 'float',
            'sale_unit_id' => 'integer',
            'net_unit_price' => 'float',
            'discount' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
            'is_packing' => 'boolean',
            'is_delivered' => 'boolean',
            'topping_id' => 'integer',
        ];
    }
}

