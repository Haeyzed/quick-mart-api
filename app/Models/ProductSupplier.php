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
 * ProductSupplier Model (Pivot)
 * 
 * Represents the relationship between products and suppliers with supplier-specific pricing.
 *
 * @property int $id
 * @property string $product_code
 * @property int $supplier_id
 * @property float $qty
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductSupplier extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_supplier';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_code',
        'supplier_id',
        'qty',
        'price',
    ];

    /**
     * Get the supplier for this product.
     *
     * @return BelongsTo<Supplier, self>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'supplier_id' => 'integer',
            'qty' => 'float',
            'price' => 'float',
        ];
    }
}
