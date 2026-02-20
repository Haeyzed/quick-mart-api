<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Tax
 *
 * Represents a tax rate within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for tax entities.
 *
 * @property int $id
 * @property string $name
 * @property float $rate
 * @property bool $is_active
 * @property int|null $woocommerce_tax_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Tax newModelQuery()
 * @method static Builder|Tax newQuery()
 * @method static Builder|Tax query()
 * @method static Builder|Tax active()
 * @method static Builder|Tax filter(array $filters)
 */
class Tax extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'rate',
        'is_active',
        'woocommerce_tax_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
        'woocommerce_tax_id' => 'integer',
    ];

    /**
     * Scope a query to apply dynamic filters.
     * * Applies filters for active status, search terms (checking name),
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
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Calculate tax amount for a given value.
     *
     * Applies the tax rate as a percentage of the given value.
     *
     * @param  float  $value  The amount to calculate tax on.
     * @return float The tax amount.
     */
    public function calculateTax(float $value): float
    {
        return ($value * $this->rate) / 100;
    }

    /**
     * Scope a query to only include active taxes.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the products associated with this tax.
     * * Defines a one-to-many relationship linking this tax to its respective products.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
