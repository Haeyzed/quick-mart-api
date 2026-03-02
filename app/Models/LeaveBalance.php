<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class LeaveBalance
 *
 * Represents an employee's leave balance for a specific leave type and year.
 * Handles the underlying data structure, relationships, and specific query scopes for leave balance entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_type_id
 * @property int $year
 * @property float $balance
 * @property float $used
 * @property float $carried_forward
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|LeaveBalance newModelQuery()
 * @method static Builder|LeaveBalance newQuery()
 * @method static Builder|LeaveBalance query()
 * @method static Builder|LeaveBalance filter(array $filters)
 *
 * @property-read Employee $employee
 * @property-read LeaveType $leaveType
 *
 * @method static Builder<static>|LeaveBalance customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance last30Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance last7Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance lastYear(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance monthToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance today(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance whereBalance($value)
 * @method static Builder<static>|LeaveBalance whereCarriedForward($value)
 * @method static Builder<static>|LeaveBalance whereCreatedAt($value)
 * @method static Builder<static>|LeaveBalance whereEmployeeId($value)
 * @method static Builder<static>|LeaveBalance whereId($value)
 * @method static Builder<static>|LeaveBalance whereLeaveTypeId($value)
 * @method static Builder<static>|LeaveBalance whereUpdatedAt($value)
 * @method static Builder<static>|LeaveBalance whereUsed($value)
 * @method static Builder<static>|LeaveBalance whereYear($value)
 * @method static Builder<static>|LeaveBalance yearToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveBalance yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class LeaveBalance extends Model
{
    use FilterableByDates;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'balance',
        'used',
        'carried_forward',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'balance' => 'decimal:2',
            'used' => 'decimal:2',
            'carried_forward' => 'decimal:2',
        ];
    }

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
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', (int) $filters['employee_id'])
            )
            ->when(
                ! empty($filters['leave_type_id']),
                fn (Builder $q) => $q->where('leave_type_id', (int) $filters['leave_type_id'])
            )
            ->when(
                ! empty($filters['year']),
                fn (Builder $q) => $q->where('year', (int) $filters['year'])
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }
}
