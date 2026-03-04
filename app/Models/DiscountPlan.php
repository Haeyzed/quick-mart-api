<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class DiscountPlan
 * 
 * Represents a discount plan that groups multiple discounts and can be assigned to customers.
 * Handles the underlying data structure, relationships, and specific query scopes for discount plan entities.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|DiscountPlan newModelQuery()
 * @method static Builder|DiscountPlan newQuery()
 * @method static Builder|DiscountPlan query()
 * @method static Builder|DiscountPlan active()
 * @method static Builder|DiscountPlan filter(array $filters)
 * @property-read Collection<int, Customer> $customers
 * @property-read int|null $customers_count
 * @property-read Collection<int, Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|DiscountPlan customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan last30Days(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan last7Days(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan lastYear(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan monthToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan onlyTrashed()
 * @method static Builder<static>|DiscountPlan quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan today(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan whereCreatedAt($value)
 * @method static Builder<static>|DiscountPlan whereDeletedAt($value)
 * @method static Builder<static>|DiscountPlan whereId($value)
 * @method static Builder<static>|DiscountPlan whereIsActive($value)
 * @method static Builder<static>|DiscountPlan whereName($value)
 * @method static Builder<static>|DiscountPlan whereType($value)
 * @method static Builder<static>|DiscountPlan whereUpdatedAt($value)
 * @method static Builder<static>|DiscountPlan withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|DiscountPlan withoutTrashed()
 * @method static Builder<static>|DiscountPlan yearToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlan yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class DiscountPlan extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'type',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @param array<string, mixed> $filters An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active discount plans.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the customers assigned to this discount plan.
     *
     * @return BelongsToMany<Customer>
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'discount_plan_customers')
            ->withTimestamps();
    }

    /**
     * Get the discounts in this discount plan.
     *
     * @return BelongsToMany<Discount>
     */
    public function discounts(): BelongsToMany
    {
        return $this->belongsToMany(Discount::class, 'discount_plan_discounts')
            ->withTimestamps();
    }
}
