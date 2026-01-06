<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * ProductPurchase Model (Pivot)
 *
 * Represents the relationship between products and purchases with additional purchase-specific data.
 *
 * @property int $id
 * @property int $purchase_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property float $recieved
 * @property float $return_qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $net_unit_price
 * @property float|null $net_unit_margin
 * @property string|null $net_unit_margin_type
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Purchase $purchase
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 *
 * @method static Builder|ProductPurchase received()
 */
class ProductPurchase extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_purchases';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'product_id',
        'product_batch_id',
        'variant_id',
        'imei_number',
        'qty',
        'recieved',
        'return_qty',
        'purchase_unit_id',
        'net_unit_cost',
        'net_unit_price',
        'net_unit_margin',
        'net_unit_margin_type',
        'discount',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the purchase that owns this product purchase.
     *
     * @return BelongsTo<Purchase, self>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the product for this purchase.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this product purchase.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this product purchase.
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
     * Check if the item is fully received.
     *
     * @return bool
     */
    public function isFullyReceived(): bool
    {
        return $this->recieved >= $this->qty;
    }

    /**
     * Scope a query to only include received items.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeReceived(Builder $query): Builder
    {
        return $query->whereColumn('recieved', '>=', 'qty');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_id' => 'integer',
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
            'qty' => 'float',
            'recieved' => 'float',
            'return_qty' => 'float',
            'purchase_unit_id' => 'integer',
            'net_unit_cost' => 'float',
            'net_unit_price' => 'float',
            'net_unit_margin' => 'float',
            'discount' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }
}

