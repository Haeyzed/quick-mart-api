<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Leave Model
 *
 * Represents a leave request for an employee.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_types
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $days
 * @property string $status
 * @property int|null $approver_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read LeaveType $leaveType
 * @property-read User|null $approver
 *
 * @method static Builder|Leave pending()
 * @method static Builder|Leave approved()
 * @method static Builder|Leave rejected()
 */
class Leave extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'leaves';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'leave_types',
        'start_date',
        'end_date',
        'days',
        'status',
        'approver_id',
    ];

    /**
     * Get the employee for this leave.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the leave type for this leave.
     *
     * @return BelongsTo<LeaveType, self>
     */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class, 'leave_types');
    }

    /**
     * Get the approver for this leave.
     *
     * @return BelongsTo<User, self>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope a query to only include pending leaves.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved leaves.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include rejected leaves.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'leave_types' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
            'days' => 'integer',
            'approver_id' => 'integer',
        ];
    }
}
