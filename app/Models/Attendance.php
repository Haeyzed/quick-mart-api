<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatusEnum;
use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class Attendance
 * 
 * Represents an employee's attendance record within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for attendance entities.
 *
 * @property int $id
 * @property string $date
 * @property int $employee_id
 * @property int $user_id
 * @property string $checkin
 * @property string|null $checkout
 * @property AttendanceStatusEnum $status
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Attendance newModelQuery()
 * @method static Builder|Attendance newQuery()
 * @method static Builder|Attendance query()
 * @method static Builder|Attendance filter(array $filters)
 * @property-read Employee $employee
 * @property-read User $user
 * @property-read Shift|null $shift
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Attendance customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Attendance last30Days(string $column = 'created_at')
 * @method static Builder<static>|Attendance last7Days(string $column = 'created_at')
 * @method static Builder<static>|Attendance lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Attendance lastYear(string $column = 'created_at')
 * @method static Builder<static>|Attendance monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Attendance onlyTrashed()
 * @method static Builder<static>|Attendance quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Attendance today(string $column = 'created_at')
 * @method static Builder<static>|Attendance whereCheckin($value)
 * @method static Builder<static>|Attendance whereCheckinSource($value)
 * @method static Builder<static>|Attendance whereCheckout($value)
 * @method static Builder<static>|Attendance whereCreatedAt($value)
 * @method static Builder<static>|Attendance whereDate($value)
 * @method static Builder<static>|Attendance whereDeletedAt($value)
 * @method static Builder<static>|Attendance whereEarlyExitMinutes($value)
 * @method static Builder<static>|Attendance whereEmployeeId($value)
 * @method static Builder<static>|Attendance whereId($value)
 * @method static Builder<static>|Attendance whereLateMinutes($value)
 * @method static Builder<static>|Attendance whereLatitude($value)
 * @method static Builder<static>|Attendance whereLongitude($value)
 * @method static Builder<static>|Attendance whereNote($value)
 * @method static Builder<static>|Attendance whereOvertimeMinutes($value)
 * @method static Builder<static>|Attendance whereShiftId($value)
 * @method static Builder<static>|Attendance whereStatus($value)
 * @method static Builder<static>|Attendance whereUpdatedAt($value)
 * @method static Builder<static>|Attendance whereUserId($value)
 * @method static Builder<static>|Attendance whereWorkedHours($value)
 * @method static Builder<static>|Attendance withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Attendance withoutTrashed()
 * @method static Builder<static>|Attendance yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Attendance yesterday(string $column = 'current_at')
 * @property int|null $shift_id
 * @property int $late_minutes
 * @property int $early_exit_minutes
 * @property float|null $worked_hours
 * @property float $overtime_minutes
 * @property string|null $checkin_source
 * @property float|null $latitude
 * @property float|null $longitude
 * @mixin Eloquent
 */
class Attendance extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attendances';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'employee_id',
        'user_id',
        'shift_id',
        'checkin',
        'checkout',
        'late_minutes',
        'early_exit_minutes',
        'worked_hours',
        'overtime_minutes',
        'checkin_source',
        'latitude',
        'longitude',
        'status',
        'note',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'status' => AttendanceStatusEnum::class,
        'late_minutes' => 'integer',
        'early_exit_minutes' => 'integer',
        'worked_hours' => 'float',
        'overtime_minutes' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',
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
                !empty($filters['employee_id']),
                fn(Builder $q) => $q->where('employee_id', $filters['employee_id'])
            )
            ->when(
                !empty($filters['user_id']),
                fn(Builder $q) => $q->where('user_id', $filters['user_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('note', 'like', $term)
                        ->orWhereHas('employee', function (Builder $subQ) use ($term) {
                            $subQ->where('name', 'like', $term);
                        });
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the employee associated with this attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who recorded this attendance.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the shift associated with this attendance record.
     *
     * @return BelongsTo<Shift, self>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
