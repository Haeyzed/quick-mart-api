<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class Designation
 * 
 * Represents an employee job designation/title within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for designation entities.
 *
 * @property int $id
 * @property string $name
 * @property int $department_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Designation newModelQuery()
 * @method static Builder|Designation newQuery()
 * @method static Builder|Designation query()
 * @method static Builder|Designation active()
 * @method static Builder|Designation filter(array $filters)
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Collection<int, Employee> $employees
 * @property-read int|null $employees_count
 * @property-read Department $department
 * @method static Builder<static>|Designation customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Designation last30Days(string $column = 'created_at')
 * @method static Builder<static>|Designation last7Days(string $column = 'created_at')
 * @method static Builder<static>|Designation lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Designation lastYear(string $column = 'created_at')
 * @method static Builder<static>|Designation monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Designation onlyTrashed()
 * @method static Builder<static>|Designation quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Designation today(string $column = 'created_at')
 * @method static Builder<static>|Designation whereCreatedAt($value)
 * @method static Builder<static>|Designation whereDeletedAt($value)
 * @method static Builder<static>|Designation whereDepartmentId($value)
 * @method static Builder<static>|Designation whereId($value)
 * @method static Builder<static>|Designation whereIsActive($value)
 * @method static Builder<static>|Designation whereName($value)
 * @method static Builder<static>|Designation whereUpdatedAt($value)
 * @method static Builder<static>|Designation withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Designation withoutTrashed()
 * @method static Builder<static>|Designation yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Designation yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class Designation extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'department_id',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'department_id' => 'integer',
        'is_active' => 'boolean',
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
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['department_id']),
                fn(Builder $q) => $q->where('department_id', $filters['department_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active designations.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the employees associated with this designation.
     *
     * Defines a one-to-many relationship linking this designation to its employees.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'designation_id');
    }

    /**
     * Get the department associated with this designation.
     *
     * Defines an inverse relationship linking this designation to its parent department.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
