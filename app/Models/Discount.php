<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Discount
 *
 * Represents a discount rule that can be applied to products. Handles the underlying data
 * structure, relationships, and specific query scopes for discount entities.
 *
 * @property int $id
 * @property string $name
 * @property string $applicable_for
 * @property array<int>|null $product_list
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_till
 * @property string $type
 * @property float $value
 * @property int|null $minimum_qty
 * @property int|null $maximum_qty
 * @property array<string>|null $days
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Discount newModelQuery()
 * @method static Builder|Discount newQuery()
 * @method static Builder|Discount query()
 * @method static Builder|Discount active()
 * @method static Builder|Discount valid()
 * @method static Builder|Discount filter(array $filters)
 *
 * @property-read Collection<int, DiscountPlan> $discountPlans
 * @property-read int|null $discount_plans_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Discount customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Discount last30Days(string $column = 'created_at')
 * @method static Builder<static>|Discount last7Days(string $column = 'created_at')
 * @method static Builder<static>|Discount lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Discount lastYear(string $column = 'created_at')
 * @method static Builder<static>|Discount monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Discount onlyTrashed()
 * @method static Builder<static>|Discount quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Discount today(string $column = 'created_at')
 * @method static Builder<static>|Discount whereApplicableFor($value)
 * @method static Builder<static>|Discount whereCreatedAt($value)
 * @method static Builder<static>|Discount whereDays($value)
 * @method static Builder<static>|Discount whereDeletedAt($value)
 * @method static Builder<static>|Discount whereId($value)
 * @method static Builder<static>|Discount whereIsActive($value)
 * @method static Builder<static>|Discount whereMaximumQty($value)
 * @method static Builder<static>|Discount whereMinimumQty($value)
 * @method static Builder<static>|Discount whereName($value)
 * @method static Builder<static>|Discount whereProductList($value)
 * @method static Builder<static>|Discount whereType($value)
 * @method static Builder<static>|Discount whereUpdatedAt($value)
 * @method static Builder<static>|Discount whereValidFrom($value)
 * @method static Builder<static>|Discount whereValidTill($value)
 * @method static Builder<static>|Discount whereValue($value)
 * @method static Builder<static>|Discount withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Discount withoutTrashed()
 * @method static Builder<static>|Discount yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Discount yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Discount extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'applicable_for',
        'product_list',
        'valid_from',
        'valid_till',
        'type',
        'value',
        'minimum_qty',
        'maximum_qty',
        'days',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'valid_from' => 'date',
        'valid_till' => 'date',
        'value' => 'float',
        'minimum_qty' => 'integer',
        'maximum_qty' => 'integer',
        'is_active' => 'boolean',
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
                isset($filters['is_active']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                isset($filters['valid']),
                fn (Builder $q) => $q->valid()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the discount plans that use this discount.
     *
     * @return BelongsToMany<DiscountPlan>
     */
    public function discountPlans(): BelongsToMany
    {
        return $this->belongsToMany(DiscountPlan::class, 'discount_plan_discounts')
            ->withTimestamps();
    }

    /**
     * Calculate discount amount for a given price and quantity.
     */
    public function calculateDiscount(float $price, int $quantity = 1): float
    {
        if (! $this->isValid()) {
            return 0;
        }

        if ($this->minimum_qty && $quantity < $this->minimum_qty) {
            return 0;
        }

        if ($this->maximum_qty && $quantity > $this->maximum_qty) {
            return 0;
        }

        return match ($this->type) {
            'percentage' => ($price * $this->value) / 100,
            'fixed' => min($this->value, $price),
            default => 0,
        };
    }

    /**
     * Check if the discount is currently valid.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }

        if ($this->valid_till && $now->gt($this->valid_till)) {
            return false;
        }

        return true;
    }

    /**
     * Scope a query to only include active discounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include valid discounts.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('valid_from')
                    ->orWhere('valid_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('valid_till')
                    ->orWhere('valid_till', '>=', now());
            });
    }

    /**
     * Get the product list as an array.
     *
     * @return array<int>|null
     */
    protected function getProductListAttribute(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        return array_filter(array_map('intval', explode(',', $value)));
    }

    /**
     * Set the product list from an array or string.
     *
     * @param  array<int>|string|null  $value
     */
    protected function setProductListAttribute(array|string|null $value): void
    {
        if (is_array($value)) {
            $this->attributes['product_list'] = implode(',', array_filter($value));
        } elseif (is_string($value) && ! empty($value)) {
            $this->attributes['product_list'] = $value;
        } else {
            $this->attributes['product_list'] = null;
        }
    }

    /**
     * Get the days as an array.
     *
     * @return array<string>|null
     */
    protected function getDaysAttribute(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        return array_filter(explode(',', $value));
    }

    /**
     * Set the days from an array or string.
     *
     * @param  array<string>|string|null  $value
     */
    protected function setDaysAttribute(array|string|null $value): void
    {
        if (is_array($value)) {
            $this->attributes['days'] = implode(',', array_filter($value));
        } elseif (is_string($value) && ! empty($value)) {
            $this->attributes['days'] = $value;
        } else {
            $this->attributes['days'] = null;
        }
    }
}
