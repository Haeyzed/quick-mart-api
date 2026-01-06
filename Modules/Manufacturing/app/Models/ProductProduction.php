<?php

declare(strict_types=1);

namespace Modules\Manufacturing\Models;

use App\Models\Product;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ProductProduction Model (Pivot)
 *
 * Represents the relationship between productions and products with production details.
 *
 * @property int $id
 * @property int $production_id
 * @property int $product_id
 * @property float $qty
 * @property float $recieved
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Production $production
 * @property-read Product $product
 * @property-read Unit $unit
 */
class ProductProduction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'production_id',
        'product_id',
        'qty',
        'recieved',
        'purchase_unit_id',
        'net_unit_cost',
        'tax_rate',
        'tax',
        'total',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'production_id' => 'integer',
            'product_id' => 'integer',
            'qty' => 'float',
            'recieved' => 'float',
            'purchase_unit_id' => 'integer',
            'net_unit_cost' => 'float',
            'tax_rate' => 'float',
            'tax' => 'float',
            'total' => 'float',
        ];
    }

    /**
     * Get the production that owns this product production.
     *
     * @return BelongsTo<Production, self>
     */
    public function production(): BelongsTo
    {
        return $this->belongsTo(Production::class);
    }

    /**
     * Get the product for this production.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the unit for this product production.
     *
     * @return BelongsTo<Unit, self>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'purchase_unit_id');
    }
}
