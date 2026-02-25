<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\OvertimeStatusEnum;
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
 * Class Overtime
 * 
 * Represents an overtime record within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for overtime entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $date
 * @property float $hours
 * @property float $rate
 * @property float $amount
 * @property string $status
 * @property int|null $approved_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Overtime newModelQuery()
 * @method static Builder|Overtime newQuery()
 * @method static Builder|Overtime query()
 * @method static Builder|Overtime filter(array $filters)
 * @property-read \App\Models\User|null $approver
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\Employee $employee
 * @method static Builder<static>|Overtime customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Overtime last30Days(string $column = 'created_at')
 * @method static Builder<static>|Overtime last7Days(string $column = 'created_at')
 * @method static Builder<static>|Overtime lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Overtime lastYear(string $column = 'created_at')
 * @method static Builder<static>|Overtime monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Overtime onlyTrashed()
 * @method static Builder<static>|Overtime quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Overtime today(string $column = 'created_at')
 * @method static Builder<static>|Overtime whereAmount($value)
 * @method static Builder<static>|Overtime whereCreatedAt($value)
 * @method static Builder<static>|Overtime whereDate($value)
 * @method static Builder<static>|Overtime whereEmployeeId($value)
 * @method static Builder<static>|Overtime whereHours($value)
 * @method static Builder<static>|Overtime whereId($value)
 * @method static Builder<static>|Overtime whereRate($value)
 * @method static Builder<static>|Overtime whereStatus($value)
 * @method static Builder<static>|Overtime whereUpdatedAt($value)
 * @method static Builder<static>|Overtime withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Overtime withoutTrashed()
 * @method static Builder<static>|Overtime yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Overtime yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Overtime extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'overtimes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'hours',
        'rate',
        'amount',
        'status',
        'approved_by'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date:Y-m-d',
        'hours' => 'float',
        'rate' => 'float',
        'amount' => 'float',
        'status' => OvertimeStatusEnum::class,
    ];

    /**
     * The "booted" method of the model.
     * * Automatically calculates the overtime amount before saving to the database.
     *
     * @return void
     */
    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Overtime $overtime) {
            $overtime->amount = (float) $overtime->hours * (float) $overtime->rate;
        });
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
     * Get the employee associated with this overtime record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who approved or rejected this overtime.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
