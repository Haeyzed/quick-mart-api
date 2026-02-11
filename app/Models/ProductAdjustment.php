<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * ProductAdjustment Model (Pivot)
 *
 * Represents the relationship between adjustments and products with adjustment details.
 *
 * @property int $id
 * @property int $adjustment_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property float $unit_cost
 * @property float $qty
 * @property string $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Adjustment $adjustment
 * @property-read Product $product
 * @property-read Variant|null $variant
 *
 * @method static Builder|ProductAdjustment add()
 * @method static Builder|ProductAdjustment subtract()
 */
class ProductAdjustment extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_adjustments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'adjustment_id',
        'product_id',
        'variant_id',
        'unit_cost',
        'qty',
        'action',
    ];

    /**
     * Get the adjustment that owns this product adjustment.
     *
     * @return BelongsTo<Adjustment, self>
     */
    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(Adjustment::class);
    }

    /**
     * Get the product for this adjustment.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant for this product adjustment.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Scope a query to only include add actions.
     */
    public function scopeAdd(Builder $query): Builder
    {
        return $query->where('action', 'add');
    }

    /**
     * Scope a query to only include subtract actions.
     */
    public function scopeSubtract(Builder $query): Builder
    {
        return $query->where('action', 'subtract');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'adjustment_id' => 'integer',
            'product_id' => 'integer',
            'variant_id' => 'integer',
            'unit_cost' => 'float',
            'qty' => 'float',
        ];
    }
}
