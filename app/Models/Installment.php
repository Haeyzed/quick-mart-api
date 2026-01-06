<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Installment Model
 *
 * Represents an installment payment in an installment plan.
 *
 * @property int $id
 * @property int $installment_plan_id
 * @property string $status
 * @property Carbon|null $payment_date
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read InstallmentPlan $plan
 *
 * @method static Builder|Installment paid()
 * @method static Builder|Installment pending()
 * @method static Builder|Installment overdue()
 */
class Installment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'installment_plan_id',
        'status',
        'payment_date',
        'amount',
    ];

    /**
     * Get the installment plan for this installment.
     *
     * @return BelongsTo<InstallmentPlan, self>
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id');
    }

    /**
     * Check if the installment is paid.
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the installment is overdue.
     *
     * @return bool
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->payment_date && now()->isAfter($this->payment_date);
    }

    /**
     * Scope a query to only include paid installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include overdue installments.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('payment_date', '<', now());
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'installment_plan_id' => 'integer',
            'payment_date' => 'date',
            'amount' => 'float',
        ];
    }
}

