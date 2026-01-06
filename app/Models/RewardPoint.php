<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * RewardPoint Model
 *
 * Represents a reward point transaction for a customer.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $reward_point_type
 * @property float $points
 * @property float $deducted_points
 * @property string|null $note
 * @property Carbon|null $expired_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $sale_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Customer $customer
 * @property-read User|null $creator
 * @property-read Sale|null $sale
 *
 * @method static Builder|RewardPoint expired()
 * @method static Builder|RewardPoint notExpired()
 */
class RewardPoint extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'reward_points';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'reward_point_type',
        'points',
        'deducted_points',
        'note',
        'expired_at',
        'created_by',
        'updated_by',
        'sale_id',
    ];

    /**
     * Get the customer for this reward point.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this reward point.
     *
     * @return BelongsTo<User, self>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the sale associated with this reward point.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Check if the reward point is expired.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->expired_at && now()->isAfter($this->expired_at);
    }

    /**
     * Get the net points (points - deducted_points).
     *
     * @return float
     */
    public function getNetPoints(): float
    {
        return $this->points - $this->deducted_points;
    }

    /**
     * Scope a query to only include expired reward points.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_at', '<', now());
    }

    /**
     * Scope a query to only include non-expired reward points.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expired_at')
                ->orWhere('expired_at', '>', now());
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
            'customer_id' => 'integer',
            'points' => 'float',
            'deducted_points' => 'float',
            'expired_at' => 'datetime',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'sale_id' => 'integer',
        ];
    }
}

