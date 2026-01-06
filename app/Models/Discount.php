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
 * Discount Model
 *
 * Represents a discount rule that can be applied to products.
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
 *
 * @property-read Collection<int, DiscountPlan> $discountPlans
 *
 * @method static Builder|Discount active()
 * @method static Builder|Discount valid()
 */
class Discount extends Model
{
    use HasFactory, SoftDeletes;

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
     *
     * @param float $price
     * @param int $quantity
     * @return float
     */
    public function calculateDiscount(float $price, int $quantity = 1): float
    {
        if (!$this->isValid()) {
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
     *
     * @return bool
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
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
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include valid discounts.
     *
     * @param Builder $query
     * @return Builder
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'valid_from' => 'date',
            'valid_till' => 'date',
            'value' => 'float',
            'minimum_qty' => 'integer',
            'maximum_qty' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the product list as an array.
     *
     * @param string|null $value
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
     * @param array<int>|string|null $value
     * @return void
     */
    protected function setProductListAttribute(array|string|null $value): void
    {
        if (is_array($value)) {
            $this->attributes['product_list'] = implode(',', array_filter($value));
        } elseif (is_string($value) && !empty($value)) {
            $this->attributes['product_list'] = $value;
        } else {
            $this->attributes['product_list'] = null;
        }
    }

    /**
     * Get the days as an array.
     *
     * @param string|null $value
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
     * @param array<string>|string|null $value
     * @return void
     */
    protected function setDaysAttribute(array|string|null $value): void
    {
        if (is_array($value)) {
            $this->attributes['days'] = implode(',', array_filter($value));
        } elseif (is_string($value) && !empty($value)) {
            $this->attributes['days'] = $value;
        } else {
            $this->attributes['days'] = null;
        }
    }
}

