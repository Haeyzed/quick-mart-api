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
 * Represents a measurement unit within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for unit entities.
 * Supports base-unit conversion (e.g. kg as base, g as derived).
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
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_unit' => 'integer',
        'operation_value' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to apply dynamic filters.
     * * Applies filters for active status, search terms (checking name and code),
     * and date ranges via the FilterableByDates trait.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active units.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the base unit for this unit.
     * * Defines a many-to-one relationship linking this unit to its base unit.
     */
    public function baseUnitRelation(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit');
    }

    /**
     * Get the sub-units that use this unit as base.
     * * Defines a one-to-many relationship linking this unit to its derived units.
     */
    public function subUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit');
    }

    /**
     * Get the products associated with this unit.
     * * Defines a one-to-many relationship linking this unit to its respective products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
