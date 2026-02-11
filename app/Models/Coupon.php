<?php

declare(strict_types=1);

namespace App\Models;

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
 * Coupon Model
 *
 * Represents a discount coupon code.
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
 * @property-read User|null $user
 * @property-read Collection<int, Sale> $sales
 *
 * @method static Builder|Coupon active()
 * @method static Builder|Coupon valid()
 * @method static Builder|Coupon expired()
 */
class Coupon extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'minimum_amount' => 'float',
            'user_id' => 'integer',
            'quantity' => 'integer',
            'used' => 'integer',
            'expired_date' => 'date',
            'is_active' => 'boolean',
        ];
    }
}
