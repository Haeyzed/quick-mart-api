<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Customer Model
 *
 * Represents a customer in the system.
 *
 * @property int $id
 * @property int|null $customer_group_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $company_name
 * @property string|null $email
 * @property string $type
 * @property string|null $phone_number
 * @property string|null $wa_number
 * @property string|null $tax_no
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property float $opening_balance
 * @property float $credit_limit
 * @property float $points
 * @property float $deposit
 * @property int|null $pay_term_no
 * @property string|null $pay_term_period
 * @property float $expense
 * @property string|null $wishlist
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read CustomerGroup|null $customerGroup
 * @property-read User|null $user
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, DiscountPlan> $discountPlans
 * @property-read Collection<int, RewardPoint> $rewardPoints
 * @property-read Collection<int, Deposit> $deposits
 *
 * @method static Builder|Customer active()
 */
class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_group_id',
        'user_id',
        'name',
        'company_name',
        'email',
        'type',
        'phone_number',
        'wa_number',
        'tax_no',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'opening_balance',
        'credit_limit',
        'points',
        'deposit',
        'pay_term_no',
        'pay_term_period',
        'expense',
        'wishlist',
        'is_active',
    ];

    /**
     * Get the customer group for this customer.
     *
     * @return BelongsTo<CustomerGroup, self>
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Get the user account associated with this customer.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the discount plans for this customer.
     *
     * @return BelongsToMany<DiscountPlan>
     */
    public function discountPlans(): BelongsToMany
    {
        return $this->belongsToMany(DiscountPlan::class, 'discount_plan_customers')
            ->withTimestamps();
    }

    /**
     * Get the reward points for this customer.
     *
     * @return HasMany<RewardPoint>
     */
    public function rewardPoints(): HasMany
    {
        return $this->hasMany(RewardPoint::class);
    }

    /**
     * Get the deposits for this customer.
     *
     * @return HasMany<Deposit>
     */
    public function deposits(): HasMany
    {
        return $this->hasMany(Deposit::class);
    }

    /**
     * Calculate the total due amount for this customer.
     *
     * @return float
     */
    public function getTotalDue(): float
    {
        $totalSales = $this->sales()->where('payment_status', '!=', 'paid')->sum('grand_total');
        $totalPaid = $this->sales()->sum('paid_amount');

        return max(0, $totalSales - $totalPaid);
    }

    /**
     * Get the sales for this customer.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope a query to only include active customers.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'customer_group_id' => 'integer',
            'user_id' => 'integer',
            'opening_balance' => 'float',
            'credit_limit' => 'float',
            'points' => 'float',
            'deposit' => 'float',
            'pay_term_no' => 'integer',
            'expense' => 'float',
            'is_active' => 'boolean',
        ];
    }
}

