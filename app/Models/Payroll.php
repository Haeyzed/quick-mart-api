<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PayrollStatusEnum;
use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Payroll
 * 
 * Represents an employee's payroll record within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for payroll entities.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $employee_id
 * @property int $account_id
 * @property int $user_id
 * @property float $amount
 * @property string $paying_method
 * @property string|null $note
 * @property PayrollStatusEnum $status
 * @property array|null $amount_array
 * @property string $month
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Payroll newModelQuery()
 * @method static Builder|Payroll newQuery()
 * @method static Builder|Payroll query()
 * @method static Builder|Payroll filter(array $filters)
 * @property-read \App\Models\Account $account
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\User $user
 * @method static Builder<static>|Payroll customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Payroll last30Days(string $column = 'created_at')
 * @method static Builder<static>|Payroll last7Days(string $column = 'created_at')
 * @method static Builder<static>|Payroll lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Payroll lastYear(string $column = 'created_at')
 * @method static Builder<static>|Payroll monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Payroll onlyTrashed()
 * @method static Builder<static>|Payroll quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Payroll today(string $column = 'created_at')
 * @method static Builder<static>|Payroll whereAccountId($value)
 * @method static Builder<static>|Payroll whereAmount($value)
 * @method static Builder<static>|Payroll whereAmountArray($value)
 * @method static Builder<static>|Payroll whereCreatedAt($value)
 * @method static Builder<static>|Payroll whereEmployeeId($value)
 * @method static Builder<static>|Payroll whereId($value)
 * @method static Builder<static>|Payroll whereMonth($value)
 * @method static Builder<static>|Payroll whereNote($value)
 * @method static Builder<static>|Payroll wherePayingMethod($value)
 * @method static Builder<static>|Payroll whereReferenceNo($value)
 * @method static Builder<static>|Payroll whereStatus($value)
 * @method static Builder<static>|Payroll whereUpdatedAt($value)
 * @method static Builder<static>|Payroll whereUserId($value)
 * @method static Builder<static>|Payroll withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Payroll withoutTrashed()
 * @method static Builder<static>|Payroll yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Payroll yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Payroll extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'employee_id',
        'account_id',
        'user_id',
        'amount',
        'paying_method',
        'note',
        'status',
        'amount_array',
        'month'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'amount_array' => 'array',
        'status' => PayrollStatusEnum::class,
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', $filters['employee_id'])
            )
            ->when(
                ! empty($filters['month']),
                fn (Builder $q) => $q->where('month', $filters['month'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('reference_no', 'like', $term)
                        ->orWhereHas('employee', function (Builder $subQ) use ($term) {
                            $subQ->where('name', 'like', $term);
                        });
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the employee associated with this payroll record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this payroll record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the account from which the payroll was paid.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
