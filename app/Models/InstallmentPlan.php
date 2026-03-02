<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class InstallmentPlan
 *
 * Represents an installment payment plan for a sale or purchase. Handles the underlying data
 * structure, relationships, and specific query scopes for installment plan entities.
 *
 * @property int $id
 * @property string $reference_type
 * @property int $reference_id
 * @property string $name
 * @property float $price
 * @property float $additional_amount
 * @property float $total_amount
 * @property float $down_payment
 * @property int $months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|InstallmentPlan newModelQuery()
 * @method static Builder|InstallmentPlan newQuery()
 * @method static Builder|InstallmentPlan query()
 * @method static Builder|InstallmentPlan filter(array $filters)
 *
 * @property-read Model $reference
 * @property-read Collection<int, Installment> $installments
 * @property-read int|null $installments_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|InstallmentPlan customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan last30Days(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan last7Days(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan lastYear(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan monthToDate(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan today(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan whereAdditionalAmount($value)
 * @method static Builder<static>|InstallmentPlan whereCreatedAt($value)
 * @method static Builder<static>|InstallmentPlan whereDownPayment($value)
 * @method static Builder<static>|InstallmentPlan whereId($value)
 * @method static Builder<static>|InstallmentPlan whereMonths($value)
 * @method static Builder<static>|InstallmentPlan whereName($value)
 * @method static Builder<static>|InstallmentPlan wherePrice($value)
 * @method static Builder<static>|InstallmentPlan whereReferenceId($value)
 * @method static Builder<static>|InstallmentPlan whereReferenceType($value)
 * @method static Builder<static>|InstallmentPlan whereTotalAmount($value)
 * @method static Builder<static>|InstallmentPlan whereUpdatedAt($value)
 * @method static Builder<static>|InstallmentPlan yearToDate(string $column = 'created_at')
 * @method static Builder<static>|InstallmentPlan yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class InstallmentPlan extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_type',
        'reference_id',
        'name',
        'price',
        'additional_amount',
        'total_amount',
        'down_payment',
        'months',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reference_id' => 'integer',
        'price' => 'float',
        'additional_amount' => 'float',
        'total_amount' => 'float',
        'down_payment' => 'float',
        'months' => 'integer',
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
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the parent reference model (Sale or Purchase).
     *
     * @return MorphTo<Model, self>
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the installments for this plan.
     *
     * @return HasMany<Installment>
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    /**
     * Calculate the monthly installment amount.
     */
    public function getMonthlyAmount(): float
    {
        if ($this->months <= 0) {
            return 0;
        }

        return ($this->total_amount - $this->down_payment) / $this->months;
    }
}
