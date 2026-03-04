<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * ProductReturn Model (Pivot)
 * 
 * Represents the relationship between returns and products with return-specific data.
 *
 * @property int $id
 * @property int $return_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property int|null $product_batch_id
 * @property float $qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Returns $return
 * @property-read Product $product
 * @property-read Variant|null $variant
 * @property-read ProductBatch|null $batch
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|ProductReturn newModelQuery()
 * @method static Builder<static>|ProductReturn newQuery()
 * @method static Builder<static>|ProductReturn query()
 * @method static Builder<static>|ProductReturn whereCreatedAt($value)
 * @method static Builder<static>|ProductReturn whereDiscount($value)
 * @method static Builder<static>|ProductReturn whereId($value)
 * @method static Builder<static>|ProductReturn whereImeiNumber($value)
 * @method static Builder<static>|ProductReturn whereNetUnitPrice($value)
 * @method static Builder<static>|ProductReturn whereProductBatchId($value)
 * @method static Builder<static>|ProductReturn whereProductId($value)
 * @method static Builder<static>|ProductReturn whereQty($value)
 * @method static Builder<static>|ProductReturn whereReturnId($value)
 * @method static Builder<static>|ProductReturn whereSaleUnitId($value)
 * @method static Builder<static>|ProductReturn whereTax($value)
 * @method static Builder<static>|ProductReturn whereTaxRate($value)
 * @method static Builder<static>|ProductReturn whereTotal($value)
 * @method static Builder<static>|ProductReturn whereUpdatedAt($value)
 * @method static Builder<static>|ProductReturn whereVariantId($value)
 * @mixin Eloquent
 */
class ProductReturn extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_returns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'return_id',
        'product_id',
        'variant_id',
        'imei_number',
        'product_batch_id',
        'qty',
        'sale_unit_id',
        'net_unit_price',
        'discount',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the return that owns this product return.
     *
     * @return BelongsTo<Returns, self>
     */
    public function return(): BelongsTo
    {
        return $this->belongsTo(Returns::class, 'return_id');
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
     * Get the variant for this product return.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'return_id' => 'integer',
            'product_id' => 'integer',
            'variant_id' => 'integer',
            'product_batch_id' => 'integer',
            'qty' => 'float',
            'sale_unit_id' => 'integer',
            'net_unit_price' => 'float',
            'discount' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }
}
