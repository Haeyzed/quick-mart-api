<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeaveStatusEnum;
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
 * Class Leave
 *
 * Represents a leave request within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for leave entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_types
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property float $days
 * @property string $status
 * @property int|null $approver_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Leave newModelQuery()
 * @method static Builder|Leave newQuery()
 * @method static Builder|Leave query()
 * @method static Builder|Leave filter(array $filters)
 *
 * @property-read \App\Models\User|null $approver
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\LeaveType $leaveType
 *
 * @method static Builder<static>|Leave customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Leave last30Days(string $column = 'created_at')
 * @method static Builder<static>|Leave last7Days(string $column = 'created_at')
 * @method static Builder<static>|Leave lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Leave lastYear(string $column = 'created_at')
 * @method static Builder<static>|Leave monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Leave onlyTrashed()
 * @method static Builder<static>|Leave quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Leave today(string $column = 'created_at')
 * @method static Builder<static>|Leave whereApproverId($value)
 * @method static Builder<static>|Leave whereCreatedAt($value)
 * @method static Builder<static>|Leave whereDays($value)
 * @method static Builder<static>|Leave whereEmployeeId($value)
 * @method static Builder<static>|Leave whereEndDate($value)
 * @method static Builder<static>|Leave whereId($value)
 * @method static Builder<static>|Leave whereLeaveTypes($value)
 * @method static Builder<static>|Leave whereStartDate($value)
 * @method static Builder<static>|Leave whereStatus($value)
 * @method static Builder<static>|Leave whereUpdatedAt($value)
 * @method static Builder<static>|Leave withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Leave withoutTrashed()
 * @method static Builder<static>|Leave yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Leave yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Leave extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'days',
        'reason',
        'attachment',
        'status',
        'approver_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'days' => 'float',
        'status' => LeaveStatusEnum::class,
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
                isset($filters['status']),
                fn (Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->whereHas('employee', function (Builder $subQ) use ($term) {
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
     * Get the employee associated with this leave request.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type assigned to this leave request.
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /**
     * Get the user who approved or rejected this leave request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
