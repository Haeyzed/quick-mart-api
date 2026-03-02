<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Coupon
 *
 * Represents a discount coupon code. Handles the underlying data
 * structure, relationships, and specific query scopes for coupon entities.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property float $amount
 * @property float|null $minimum_amount
 * @property int|null $user_id
 * @property int $quantity
 * @property int $used
 * @property Carbon|null $expired_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Coupon newModelQuery()
 * @method static Builder|Coupon newQuery()
 * @method static Builder|Coupon query()
 * @method static Builder|Coupon active()
 * @method static Builder|Coupon valid()
 * @method static Builder|Coupon expired()
 * @method static Builder|Coupon filter(array $filters)
 *
 * @property-read \App\Models\User|null $user
 * @property-read Collection<int, Sale> $sales
 * @property-read int|null $sales_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Coupon customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Coupon last30Days(string $column = 'created_at')
 * @method static Builder<static>|Coupon last7Days(string $column = 'created_at')
 * @method static Builder<static>|Coupon lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Coupon lastYear(string $column = 'created_at')
 * @method static Builder<static>|Coupon monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Coupon quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Coupon today(string $column = 'created_at')
 * @method static Builder<static>|Coupon whereAmount($value)
 * @method static Builder<static>|Coupon whereCode($value)
 * @method static Builder<static>|Coupon whereCreatedAt($value)
 * @method static Builder<static>|Coupon whereExpiredDate($value)
 * @method static Builder<static>|Coupon whereId($value)
 * @method static Builder<static>|Coupon whereIsActive($value)
 * @method static Builder<static>|Coupon whereMinimumAmount($value)
 * @method static Builder<static>|Coupon whereName($value)
 * @method static Builder<static>|Coupon whereQuantity($value)
 * @method static Builder<static>|Coupon whereType($value)
 * @method static Builder<static>|Coupon whereUpdatedAt($value)
 * @method static Builder<static>|Coupon whereUsed($value)
 * @method static Builder<static>|Coupon whereUserId($value)
 * @method static Builder<static>|Coupon yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Coupon yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Coupon extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'type',
        'amount',
        'minimum_amount',
        'user_id',
        'quantity',
        'used',
        'expired_date',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'minimum_amount' => 'float',
        'user_id' => 'integer',
        'quantity' => 'integer',
        'used' => 'integer',
        'expired_date' => 'date',
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
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the user who created this coupon.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sales that used this coupon.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Check if the coupon is valid.
     */
    public function isValid(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->expired_date && now()->isAfter($this->expired_date)) {
            return false;
        }

        if ($this->used >= $this->quantity) {
            return false;
        }

        return true;
    }

    /**
     * Check if the coupon is expired.
     */
    public function isExpired(): bool
    {
        return $this->expired_date && now()->isAfter($this->expired_date);
    }

    /**
     * Check if the coupon is fully used.
     */
    public function isFullyUsed(): bool
    {
        return $this->used >= $this->quantity;
    }

    /**
     * Calculate discount amount for a given total.
     */
    public function calculateDiscount(float $total): float
    {
        if ($this->minimum_amount && $total < $this->minimum_amount) {
            return 0;
        }

        return match ($this->type) {
            'percentage' => ($total * $this->amount) / 100,
            'fixed' => min($this->amount, $total),
            default => 0,
        };
    }

    /**
     * Scope a query to only include active coupons.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include valid coupons.
     */
    public function scopeValid(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expired_date')
                    ->orWhere('expired_date', '>', now());
            })
            ->whereRaw('used < quantity');
    }

    /**
     * Scope a query to only include expired coupons.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_date', '<', now());
    }
}
