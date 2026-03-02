<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Adjustment
 *
 * Represents a stock quantity adjustment for a warehouse. Handles the underlying data
 * structure, relationships, and specific query scopes for adjustment entities.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $warehouse_id
 * @property string|null $document
 * @property float $total_qty
 * @property int $item
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Adjustment newModelQuery()
 * @method static Builder|Adjustment newQuery()
 * @method static Builder|Adjustment query()
 * @method static Builder|Adjustment filter(array $filters)
 *
 * @property-read \App\Models\Warehouse $warehouse
 * @property-read Collection<int, ProductAdjustment> $productAdjustments
 * @property-read int|null $product_adjustments_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Adjustment customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Adjustment last30Days(string $column = 'created_at')
 * @method static Builder<static>|Adjustment last7Days(string $column = 'created_at')
 * @method static Builder<static>|Adjustment lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Adjustment lastYear(string $column = 'created_at')
 * @method static Builder<static>|Adjustment monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Adjustment quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Adjustment today(string $column = 'created_at')
 * @method static Builder<static>|Adjustment whereCreatedAt($value)
 * @method static Builder<static>|Adjustment whereDocument($value)
 * @method static Builder<static>|Adjustment whereId($value)
 * @method static Builder<static>|Adjustment whereItem($value)
 * @method static Builder<static>|Adjustment whereNote($value)
 * @method static Builder<static>|Adjustment whereReferenceNo($value)
 * @method static Builder<static>|Adjustment whereTotalQty($value)
 * @method static Builder<static>|Adjustment whereUpdatedAt($value)
 * @method static Builder<static>|Adjustment whereWarehouseId($value)
 * @method static Builder<static>|Adjustment yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Adjustment yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Adjustment extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'document',
        'total_qty',
        'item',
        'note',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'warehouse_id' => 'integer',
        'total_qty' => 'float',
        'item' => 'integer',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['warehouse_id']),
                fn (Builder $q) => $q->where('warehouse_id', $filters['warehouse_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('reference_no', 'like', $term)
                        ->orWhere('note', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the warehouse for this adjustment.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product adjustments for this adjustment.
     *
     * @return HasMany<ProductAdjustment>
     */
    public function productAdjustments(): HasMany
    {
        return $this->hasMany(ProductAdjustment::class);
    }
}
