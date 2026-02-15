<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Unit
 *
 * Represents a measurement unit with support for base unit conversion.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $base_unit
 * @property string|null $operator
 * @property float|null $operation_value
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Unit newModelQuery()
 * @method static Builder|Unit newQuery()
 * @method static Builder|Unit query()
 * @method static Builder|Unit active()
 * @method static Builder|Unit filter(array $filters)
 */
class Unit extends Model implements AuditableContract
{
    use HasFactory, Auditable, SoftDeletes, FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'base_unit',
        'operator',
        'operation_value',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_unit' => 'integer',
        'operation_value' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn(Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                    );
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active units.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the base unit for this unit.
     */
    public function baseUnitRelation(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit');
    }

    /**
     * Get the sub-units that use this unit as base.
     */
    public function subUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit');
    }

    /**
     * Get the products using this unit.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
