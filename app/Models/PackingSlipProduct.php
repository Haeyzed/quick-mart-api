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
