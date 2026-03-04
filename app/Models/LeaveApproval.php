<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class LeaveApproval
 * 
 * Represents a single approval step in the multi-level leave approval workflow.
 * Handles the underlying data structure, relationships, and specific query scopes for leave approval entities.
 *
 * @property int $id
 * @property int $leave_id
 * @property int $level
 * @property int|null $approver_id
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|LeaveApproval newModelQuery()
 * @method static Builder|LeaveApproval newQuery()
 * @method static Builder|LeaveApproval query()
 * @method static Builder|LeaveApproval filter(array $filters)
 * @property-read Leave|null $leave
 * @property-read User|null $approver
 * @method static Builder<static>|LeaveApproval customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval last30Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval last7Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval lastYear(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval monthToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval today(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval whereApprovedAt($value)
 * @method static Builder<static>|LeaveApproval whereApproverId($value)
 * @method static Builder<static>|LeaveApproval whereCreatedAt($value)
 * @method static Builder<static>|LeaveApproval whereId($value)
 * @method static Builder<static>|LeaveApproval whereLeaveId($value)
 * @method static Builder<static>|LeaveApproval whereLevel($value)
 * @method static Builder<static>|LeaveApproval whereNotes($value)
 * @method static Builder<static>|LeaveApproval whereStatus($value)
 * @method static Builder<static>|LeaveApproval whereUpdatedAt($value)
 * @method static Builder<static>|LeaveApproval yearToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveApproval yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class LeaveApproval extends Model
{
    use FilterableByDates;

    protected $table = 'leave_approvals';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'leave_id',
        'level',
        'approver_id',
        'status',
        'notes',
        'approved_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'approved_at' => 'datetime',
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
                !empty($filters['leave_id']),
                fn(Builder $q) => $q->where('leave_id', (int)$filters['leave_id'])
            )
            ->when(
                !empty($filters['approver_id']),
                fn(Builder $q) => $q->where('approver_id', (int)$filters['approver_id'])
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the leave request this approval belongs to.
     */
    public function leave(): BelongsTo
    {
        return $this->belongsTo(Leave::class);
    }

    /**
     * Get the user who performed this approval.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
