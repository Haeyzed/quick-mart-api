<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * DiscountPlan Model
 *
 * Represents a discount plan that groups multiple discounts and can be assigned to customers.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Customer> $customers
 * @property-read Collection<int, Discount> $discounts
 *
 * @method static Builder|DiscountPlan active()
 */
class DiscountPlan extends Model
{
    use HasFactory, SoftDeletes;

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

    /**
     * Scope a query to only include active discount plans.
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
            'is_active' => 'boolean',
        ];
    }
}

