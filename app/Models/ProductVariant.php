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
 * ProductVariant Model (Pivot)
 * 
 * Represents the relationship between products and variants with variant-specific pricing and stock.
 *
 * @property int $id
 * @property int $product_id
 * @property int $variant_id
 * @property int $position
 * @property string|null $item_code
 * @property float $additional_cost
 * @property float $additional_price
 * @property float $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Variant $variant
 * @method static Builder|ProductVariant findExactProduct(int $productId, int $variantId)
 * @method static Builder|ProductVariant findExactProductWithCode(int $productId, string $itemCode)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|ProductVariant newModelQuery()
 * @method static Builder<static>|ProductVariant newQuery()
 * @method static Builder<static>|ProductVariant query()
 * @method static Builder<static>|ProductVariant whereAdditionalCost($value)
 * @method static Builder<static>|ProductVariant whereAdditionalPrice($value)
 * @method static Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static Builder<static>|ProductVariant whereId($value)
 * @method static Builder<static>|ProductVariant whereItemCode($value)
 * @method static Builder<static>|ProductVariant wherePosition($value)
 * @method static Builder<static>|ProductVariant whereProductId($value)
 * @method static Builder<static>|ProductVariant whereQty($value)
 * @method static Builder<static>|ProductVariant whereUpdatedAt($value)
 * @method static Builder<static>|ProductVariant whereVariantId($value)
 * @mixin \Eloquent
 */
class ProductVariant extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'product_variants';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'variant_id',
        'position',
        'item_code',
        'additional_cost',
        'additional_price',
        'qty',
    ];

    /**
     * Get the product for this variant.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the variant.
     *
     * @return BelongsTo<Variant, self>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * Scope a query to find exact product with variant.
     */
    public function scopeFindExactProduct(Builder $query, int $productId, int $variantId): Builder
    {
        return $query->where('product_id', $productId)
            ->where('variant_id', $variantId);
    }

    /**
     * Scope a query to find exact product with item code.
     */
    public function scopeFindExactProductWithCode(Builder $query, int $productId, string $itemCode): Builder
    {
        return $query->where('product_id', $productId)
            ->where('item_code', $itemCode);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'variant_id' => 'integer',
            'position' => 'integer',
            'additional_cost' => 'float',
            'additional_price' => 'float',
            'qty' => 'float',
        ];
    }
}
