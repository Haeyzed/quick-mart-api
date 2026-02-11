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
 * StockCount Model
 *
 * Represents a stock count/inventory audit for a warehouse.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $warehouse_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property int $user_id
 * @property string $type
 * @property string|null $initial_file
 * @property string|null $final_file
 * @property string|null $note
 * @property bool $is_adjusted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse $warehouse
 * @property-read Brand|null $brand
 * @property-read Category|null $category
 * @property-read User $user
 *
 * @method static Builder|StockCount adjusted()
 * @method static Builder|StockCount notAdjusted()
 */
class StockCount extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stock_counts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'brand_id',
        'category_id',
        'user_id',
        'type',
        'initial_file',
        'final_file',
        'note',
        'is_adjusted',
    ];

    /**
     * Get the warehouse for this stock count.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the brand filter for this stock count.
     *
     * @return BelongsTo<Brand, self>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Get the category filter for this stock count.
     *
     * @return BelongsTo<Category, self>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the user who created this stock count.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include adjusted stock counts.
     */
    public function scopeAdjusted(Builder $query): Builder
    {
        return $query->where('is_adjusted', true);
    }

    /**
     * Scope a query to only include not adjusted stock counts.
     */
    public function scopeNotAdjusted(Builder $query): Builder
    {
        return $query->where('is_adjusted', false);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'warehouse_id' => 'integer',
            'brand_id' => 'integer',
            'category_id' => 'integer',
            'user_id' => 'integer',
            'is_adjusted' => 'boolean',
        ];
    }
}
