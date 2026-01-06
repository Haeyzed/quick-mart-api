<?php

declare(strict_types=1);

namespace Modules\Manufacturing\Models;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Production Model
 *
 * Represents a production/manufacturing order.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $warehouse_id
 * @property int|null $product_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property float|null $production_cost
 * @property string $status
 * @property string|null $document
 * @property string|null $note
 * @property string|null $production_units_ids
 * @property float|null $wastage_percent
 * @property string|null $product_list
 * @property string|null $qty_list
 * @property string|null $price_list
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Warehouse $warehouse
 * @property-read Product|null $product
 * @property-read Collection<int, ProductProduction> $productProductions
 *
 * @method static Builder|Production pending()
 * @method static Builder|Production completed()
 */
class Production extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'product_id',
        'item',
        'total_qty',
        'total_tax',
        'total_cost',
        'shipping_cost',
        'grand_total',
        'production_cost',
        'status',
        'document',
        'note',
        'production_units_ids',
        'wastage_percent',
        'product_list',
        'qty_list',
        'price_list',
        'created_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'warehouse_id' => 'integer',
            'product_id' => 'integer',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_tax' => 'float',
            'total_cost' => 'float',
            'shipping_cost' => 'float',
            'grand_total' => 'float',
            'production_cost' => 'float',
            'wastage_percent' => 'float',
            'created_at' => 'datetime',
        ];
    }

    /**
     * Get the user who created this production.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for this production.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product being produced.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product productions for this production.
     *
     * @return HasMany<ProductProduction>
     */
    public function productProductions(): HasMany
    {
        return $this->hasMany(ProductProduction::class);
    }

    /**
     * Scope a query to only include pending productions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed productions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }
}
