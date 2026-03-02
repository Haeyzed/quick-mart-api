<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class EmployeeTransaction
 *
 * Represents a financial transaction for an employee (advance, loan, etc.). Handles the underlying data
 * structure, relationships, and specific query scopes for employee transaction entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $date
 * @property float $amount
 * @property string $type
 * @property string|null $description
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|EmployeeTransaction newModelQuery()
 * @method static Builder|EmployeeTransaction newQuery()
 * @method static Builder|EmployeeTransaction query()
 * @method static Builder|EmployeeTransaction advance()
 * @method static Builder|EmployeeTransaction loan()
 * @method static Builder|EmployeeTransaction filter(array $filters)
 *
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\User $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|EmployeeTransaction customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction whereAmount($value)
 * @method static Builder<static>|EmployeeTransaction whereCreatedAt($value)
 * @method static Builder<static>|EmployeeTransaction whereCreatedBy($value)
 * @method static Builder<static>|EmployeeTransaction whereDate($value)
 * @method static Builder<static>|EmployeeTransaction whereDescription($value)
 * @method static Builder<static>|EmployeeTransaction whereEmployeeId($value)
 * @method static Builder<static>|EmployeeTransaction whereId($value)
 * @method static Builder<static>|EmployeeTransaction whereType($value)
 * @method static Builder<static>|EmployeeTransaction whereUpdatedAt($value)
 * @method static Builder<static>|EmployeeTransaction yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeTransaction yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmployeeTransaction extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'type',
        'description',
        'created_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'employee_id' => 'integer',
        'date' => 'date',
        'amount' => 'float',
        'created_by' => 'integer',
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
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', (int) $filters['employee_id'])
            )
            ->when(
                ! empty($filters['type']),
                fn (Builder $q) => $q->where('type', $filters['type'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('description', 'like', $term)
                        ->orWhereHas('employee', function (Builder $empQ) use ($term) {
                            $empQ->where('name', 'like', $term);
                        })
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
                'date'
            );
    }

    /**
     * Get the employee for this transaction.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this transaction.
     *
     * @return BelongsTo<User, self>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include advance transactions.
     */
    public function scopeAdvance(Builder $query): Builder
    {
        return $query->where('type', 'advance');
    }

    /**
     * Scope a query to only include loan transactions.
     */
    public function scopeLoan(Builder $query): Builder
    {
        return $query->where('type', 'loan');
    }
}
