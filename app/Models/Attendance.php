<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AttendanceStatusEnum;
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
 *
 * @method static Builder|Attendance newModelQuery()
 * @method static Builder|Attendance newQuery()
 * @method static Builder|Attendance query()
 * @method static Builder|Attendance filter(array $filters)
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
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', $filters['employee_id'])
            )
            ->when(
                ! empty($filters['user_id']),
                fn (Builder $q) => $q->where('user_id', $filters['user_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('note', 'like', $term)
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
