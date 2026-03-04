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
 * Class Installment
 * 
 * Represents an installment payment in an installment plan. Handles the underlying data
 * structure, relationships, and specific query scopes for installment entities.
 *
 * @property int $id
 * @property int $installment_plan_id
 * @property string $status
 * @property Carbon|null $payment_date
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Installment newModelQuery()
 * @method static Builder|Installment newQuery()
 * @method static Builder|Installment query()
 * @method static Builder|Installment paid()
 * @method static Builder|Installment pending()
 * @method static Builder|Installment overdue()
 * @method static Builder|Installment filter(array $filters)
 * @property-read InstallmentPlan $plan
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Installment customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Installment last30Days(string $column = 'created_at')
 * @method static Builder<static>|Installment last7Days(string $column = 'created_at')
 * @method static Builder<static>|Installment lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Installment lastYear(string $column = 'created_at')
 * @method static Builder<static>|Installment monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Installment quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Installment today(string $column = 'created_at')
 * @method static Builder<static>|Installment whereAmount($value)
 * @method static Builder<static>|Installment whereCreatedAt($value)
 * @method static Builder<static>|Installment whereId($value)
 * @method static Builder<static>|Installment whereInstallmentPlanId($value)
 * @method static Builder<static>|Installment wherePaymentDate($value)
 * @method static Builder<static>|Installment whereStatus($value)
 * @method static Builder<static>|Installment whereUpdatedAt($value)
 * @method static Builder<static>|Installment yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Installment yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class Installment extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'installment_plan_id' => 'integer',
        'payment_date' => 'date',
        'amount' => 'float',
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
                isset($filters['status']),
                fn(Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                !empty($filters['installment_plan_id']),
                fn(Builder $q) => $q->where('installment_plan_id', (int)$filters['installment_plan_id'])
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

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
     */
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    /**
     * Check if the installment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->payment_date && now()->isAfter($this->payment_date);
    }

    /**
     * Scope a query to only include paid installments.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending installments.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include overdue installments.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')
            ->where('payment_date', '<', now());
    }
}
