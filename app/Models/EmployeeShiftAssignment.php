<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class EmployeeShiftAssignment
 *
 * Assignment of an employee to a shift with effective date range (for rotating/flex shifts).
 * Handles the underlying data structure, relationships, and specific query scopes for employee shift assignment entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property Carbon $effective_from
 * @property Carbon|null $effective_to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|EmployeeShiftAssignment newModelQuery()
 * @method static Builder|EmployeeShiftAssignment newQuery()
 * @method static Builder|EmployeeShiftAssignment query()
 * @method static Builder|EmployeeShiftAssignment filter(array $filters)
 *
 * @property-read Employee $employee
 * @property-read Shift $shift
 *
 * @method static Builder<static>|EmployeeShiftAssignment customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment whereCreatedAt($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereEffectiveFrom($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereEffectiveTo($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereEmployeeId($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereId($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereShiftId($value)
 * @method static Builder<static>|EmployeeShiftAssignment whereUpdatedAt($value)
 * @method static Builder<static>|EmployeeShiftAssignment yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeShiftAssignment yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmployeeShiftAssignment extends Model
{
    use FilterableByDates;

    protected $fillable = [
        'employee_id',
        'shift_id',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
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
                ! empty($filters['shift_id']),
                fn (Builder $q) => $q->where('shift_id', (int) $filters['shift_id'])
            )
            ->when(
                isset($filters['active']) && $filters['active'],
                fn (Builder $q) => $q->where('effective_from', '<=', now()->toDateString())
                    ->where(function ($subQ) {
                        $subQ->whereNull('effective_to')
                            ->orWhere('effective_to', '>=', now()->toDateString());
                    })
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Shift, self>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
