<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * DiscountPlanCustomer Model (Pivot)
 * 
 * Represents the relationship between discount plans and customers.
 *
 * @property int $id
 * @property int $discount_plan_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DiscountPlan $discountPlan
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereDiscountPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class DiscountPlanCustomer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_plan_id' => 'integer',
            'customer_id' => 'integer',
        ];
    }
}
