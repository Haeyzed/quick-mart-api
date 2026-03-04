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
 * ProductQuotation Model (Pivot)
 * 
 * Represents the relationship between quotations and products.
 *
 * @property int $id
 * @property int $quotation_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property float $qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Quotation $quotation
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read Unit $unit
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|ProductQuotation newModelQuery()
 * @method static Builder<static>|ProductQuotation newQuery()
 * @method static Builder<static>|ProductQuotation query()
 * @method static Builder<static>|ProductQuotation whereCreatedAt($value)
 * @method static Builder<static>|ProductQuotation whereDiscount($value)
 * @method static Builder<static>|ProductQuotation whereId($value)
 * @method static Builder<static>|ProductQuotation whereNetUnitPrice($value)
 * @method static Builder<static>|ProductQuotation whereProductBatchId($value)
 * @method static Builder<static>|ProductQuotation whereProductId($value)
 * @method static Builder<static>|ProductQuotation whereQty($value)
 * @method static Builder<static>|ProductQuotation whereQuotationId($value)
 * @method static Builder<static>|ProductQuotation whereSaleUnitId($value)
 * @method static Builder<static>|ProductQuotation whereTax($value)
 * @method static Builder<static>|ProductQuotation whereTaxRate($value)
 * @method static Builder<static>|ProductQuotation whereTotal($value)
 * @method static Builder<static>|ProductQuotation whereUpdatedAt($value)
 * @method static Builder<static>|ProductQuotation whereVariantId($value)
 * @mixin Eloquent
 */
class ProductQuotation extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_quotation';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'quotation_id',
        'product_id',
        'product_batch_id',
        'variant_id',
        'qty',
        'sale_unit_id',
        'net_unit_price',
        'discount',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the quotation that owns this product quotation.
     *
     * @return BelongsTo<Quotation, self>
     */
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    /**
     * Get the product for this quotation.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the batch for this product quotation.
     *
     * @return BelongsTo<ProductBatch, self>
     */
    public function batch(): BelongsTo
    {
        return $this->belongsTo(ProductBatch::class, 'product_batch_id');
    }

    /**
     * Get the variant for this product quotation.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Get the unit for this product quotation.
     *
     * @return BelongsTo<Unit, self>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'sale_unit_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quotation_id' => 'integer',
            'product_id' => 'integer',
            'product_batch_id' => 'integer',
            'variant_id' => 'integer',
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
