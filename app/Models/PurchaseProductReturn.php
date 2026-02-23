<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * PurchaseProductReturn Model (Pivot)
 * 
 * Represents the relationship between purchase returns and products with return-specific data.
 *
 * @property int $id
 * @property int $return_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ReturnPurchase $purchaseReturn
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereNetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereVariantId($value)
 * @mixin \Eloquent
 */
class PurchaseProductReturn extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'purchase_product_return';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_id',
        'product_id',
        'product_batch_id',
        'variant_id',
        'imei_number',
        'qty',
        'purchase_unit_id',
        'net_unit_cost',
        'discount',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the purchase return that owns this product return.
     *
     * @return BelongsTo<ReturnPurchase, self>
     */
    public function purchaseReturn(): BelongsTo
    {
        return $this->belongsTo(ReturnPurchase::class, 'return_id');
    }

    /**
     * Get the product for this return.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this product return.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this product return.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_id' => 'integer',
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
            'qty' => 'float',
            'purchase_unit_id' => 'integer',
            'net_unit_cost' => 'float',
            'discount' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }
}
