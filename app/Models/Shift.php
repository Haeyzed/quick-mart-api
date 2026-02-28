<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Shift
 *
 * Represents an employee work shift within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for shift entities.
 *
 * @property int $id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Shift newModelQuery()
 * @method static Builder|Shift newQuery()
 * @method static Builder|Shift query()
 * @method static Builder|Shift active()
 * @method static Builder|Shift filter(array $filters)
 *
 * @property int $grace_in Grace period (minutes) before marking late
 * @property int $grace_out Grace period (minutes) before marking early leave
 * @property numeric|null $total_hours Total working hours for the shift
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Employee|null $employee
 *
 * @method static Builder<static>|Shift customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Shift last30Days(string $column = 'created_at')
 * @method static Builder<static>|Shift last7Days(string $column = 'created_at')
 * @method static Builder<static>|Shift lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Shift lastYear(string $column = 'created_at')
 * @method static Builder<static>|Shift monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Shift onlyTrashed()
 * @method static Builder<static>|Shift quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Shift today(string $column = 'created_at')
 * @method static Builder<static>|Shift whereCreatedAt($value)
 * @method static Builder<static>|Shift whereEndTime($value)
 * @method static Builder<static>|Shift whereGraceIn($value)
 * @method static Builder<static>|Shift whereGraceOut($value)
 * @method static Builder<static>|Shift whereId($value)
 * @method static Builder<static>|Shift whereIsActive($value)
 * @method static Builder<static>|Shift whereName($value)
 * @method static Builder<static>|Shift whereStartTime($value)
 * @method static Builder<static>|Shift whereTotalHours($value)
 * @method static Builder<static>|Shift whereUpdatedAt($value)
 * @method static Builder<static>|Shift withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Shift withoutTrashed()
 * @method static Builder<static>|Shift yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Shift yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Shift extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_in',
        'grace_out',
        'total_hours',
        'break_duration',
        'is_rotational',
        'overtime_allowed',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_rotational' => 'boolean',
        'overtime_allowed' => 'boolean',
        'break_duration' => 'integer',
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
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the employees currently assigned to this shift (via employees.shift_id).
     *
     * @return HasMany<Employee, self>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'shift_id');
    }

    /**
     * Get the shift assignments (history) for this shift.
     *
     * @return HasMany<EmployeeShiftAssignment, self>
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    /**
     * Scope a query to only include active shifts.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
