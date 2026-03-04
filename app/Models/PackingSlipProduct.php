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
 * PackingSlipProduct Model (Pivot)
 * 
 * Represents the relationship between packing slips and products.
 *
 * @property int $id
 * @property int $packing_slip_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PackingSlip $packingSlip
 * @property-read Product $product
 * @property-read Variant|null $variant
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|PackingSlipProduct newModelQuery()
 * @method static Builder<static>|PackingSlipProduct newQuery()
 * @method static Builder<static>|PackingSlipProduct query()
 * @method static Builder<static>|PackingSlipProduct whereCreatedAt($value)
 * @method static Builder<static>|PackingSlipProduct whereId($value)
 * @method static Builder<static>|PackingSlipProduct wherePackingSlipId($value)
 * @method static Builder<static>|PackingSlipProduct whereProductId($value)
 * @method static Builder<static>|PackingSlipProduct whereUpdatedAt($value)
 * @method static Builder<static>|PackingSlipProduct whereVariantId($value)
 * @mixin Eloquent
 */
class PackingSlipProduct extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'packing_slip_id',
        'product_id',
        'variant_id',
    ];

    /**
     * Get the packing slip that owns this product.
     *
     * @return BelongsTo<PackingSlip, self>
     */
    public function packingSlip(): BelongsTo
    {
        return $this->belongsTo(PackingSlip::class);
    }

    /**
     * Get the product for this packing slip.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant for this packing slip product.
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
            'packing_slip_id' => 'integer',
            'product_id' => 'integer',
            'variant_id' => 'integer',
        ];
    }
}
