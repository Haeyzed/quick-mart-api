<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class DiscountPlanCustomer
 * 
 * Represents the relationship between discount plans and customers (Pivot).
 * Handles the underlying data structure and specific query scopes for this pivot entity.
 *
 * @property int $id
 * @property int $discount_plan_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|DiscountPlanCustomer newModelQuery()
 * @method static Builder|DiscountPlanCustomer newQuery()
 * @method static Builder|DiscountPlanCustomer query()
 * @method static Builder|DiscountPlanCustomer filter(array $filters)
 * @property-read DiscountPlan $discountPlan
 * @property-read Customer $customer
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|DiscountPlanCustomer customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer last30Days(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer last7Days(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer lastYear(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer monthToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer today(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer whereCreatedAt($value)
 * @method static Builder<static>|DiscountPlanCustomer whereCustomerId($value)
 * @method static Builder<static>|DiscountPlanCustomer whereDiscountPlanId($value)
 * @method static Builder<static>|DiscountPlanCustomer whereId($value)
 * @method static Builder<static>|DiscountPlanCustomer whereUpdatedAt($value)
 * @method static Builder<static>|DiscountPlanCustomer yearToDate(string $column = 'created_at')
 * @method static Builder<static>|DiscountPlanCustomer yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class DiscountPlanCustomer extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'discount_plan_id',
        'customer_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'discount_plan_id' => 'integer',
        'customer_id' => 'integer',
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
                !empty($filters['discount_plan_id']),
                fn(Builder $q) => $q->where('discount_plan_id', $filters['discount_plan_id'])
            )
            ->when(
                !empty($filters['customer_id']),
                fn(Builder $q) => $q->where('customer_id', $filters['customer_id'])
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the discount plan.
     *
     * @return BelongsTo<DiscountPlan, self>
     */
    public function discountPlan(): BelongsTo
    {
        return $this->belongsTo(DiscountPlan::class);
    }

    /**
     * Get the customer.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
