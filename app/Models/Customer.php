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
use Illuminate\Support\Facades\Schema;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

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
 * @property-read CustomerGroup|null $customerGroup
 * @property-read User|null $user
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, DiscountPlan> $discountPlans
 * @property-read Collection<int, RewardPoint> $rewardPoints
 * @property-read Collection<int, Deposit> $deposits
 *
 * @method static Builder|Customer active()
 * @method static Builder|Customer filter(array $filters)
 */
class Customer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
     * Get custom field values for this customer (for API resource).
     *
     * @return array<string, mixed>
     */
    public function getCustomFieldValues(): array
    {
        $cols = self::getCustomFieldColumnNames();
        $attrs = $this->getAttributes();
        $out = [];
        foreach ($cols as $col) {
            if (array_key_exists($col, $attrs)) {
                $out[$col] = $attrs[$col];
            }
        }

        return $out;
    }

    /**
     * Get custom field column names for customer that exist on the table (quick-mart-old).
     *
     * @return array<int, string>
     */
    public static function getCustomFieldColumnNames(): array
    {
        $columns = Schema::getColumnListing('customers');
        $customFields = CustomField::query()
            ->where('belongs_to', 'customer')
            ->get();

        $result = [];
        foreach ($customFields as $field) {
            $col = str_replace(' ', '_', strtolower($field->name));
            if (in_array($col, $columns, true)) {
                $result[] = $col;
            }
        }

        return $result;
    }

    /**
     * Scope a query to only include active customers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to apply filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['customer_group_id']),
                fn (Builder $q) => $q->where('customer_group_id', $filters['customer_group_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = '%'.$filters['search'].'%';
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                    );
                }
            );
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
