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
 * ProductTransfer Model (Pivot)
 * 
 * Represents the relationship between transfers and products.
 *
 * @property int $id
 * @property int $transfer_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Transfer $transfer
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read Unit $unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereNetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereVariantId($value)
 * @mixin \Eloquent
 */
class ProductTransfer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_transfer';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'transfer_id',
        'product_id',
        'product_batch_id',
        'variant_id',
        'imei_number',
        'qty',
        'purchase_unit_id',
        'net_unit_cost',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the transfer that owns this product transfer.
     *
     * @return BelongsTo<Transfer, self>
     */
    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    /**
     * Get the product for this transfer.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this product transfer.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this product transfer.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Get the unit for this product transfer.
     *
     * @return BelongsTo<Unit, self>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transfer_id' => 'integer',
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
            'qty' => 'float',
            'purchase_unit_id' => 'integer',
            'net_unit_cost' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }
}
