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
 * Represents a tax rate configuration.
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
    use HasFactory, Auditable, SoftDeletes, FilterableByDates;

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
        'woocommerce_tax_id' => 'integer',
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
                    );
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Calculate tax amount for a given value.
     */
    public function calculateTax(float $value): float
    {
        return ($value * $this->rate) / 100;
    }

    /**
     * Scope a query to only include active taxes.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the products using this tax.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
