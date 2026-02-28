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
 * Class Department
 *
 * Represents a department within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for department entities.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department query()
 * @method static Builder|Department active()
 * @method static Builder|Department filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 *
 * @method static Builder<static>|Department customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Department last30Days(string $column = 'created_at')
 * @method static Builder<static>|Department last7Days(string $column = 'created_at')
 * @method static Builder<static>|Department lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Department lastYear(string $column = 'created_at')
 * @method static Builder<static>|Department monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Department onlyTrashed()
 * @method static Builder<static>|Department quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Department today(string $column = 'created_at')
 * @method static Builder<static>|Department whereCreatedAt($value)
 * @method static Builder<static>|Department whereDeletedAt($value)
 * @method static Builder<static>|Department whereId($value)
 * @method static Builder<static>|Department whereIsActive($value)
 * @method static Builder<static>|Department whereName($value)
 * @method static Builder<static>|Department whereUpdatedAt($value)
 * @method static Builder<static>|Department withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Department withoutTrashed()
 * @method static Builder<static>|Department yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Department yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Department extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
        'parent_id',
        'manager_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'parent_id' => 'integer',
        'manager_id' => 'integer',
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
     * Scope a query to only include active departments.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the parent department (for hierarchy).
     *
     * @return BelongsTo<Department, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'parent_id');
    }

    /**
     * Get the child departments.
     *
     * @return HasMany<Department, self>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Department::class, 'parent_id');
    }

    /**
     * Get the department manager (employee).
     *
     * @return BelongsTo<Employee, self>
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'manager_id');
    }

    /**
     * Get the employees associated with this department.
     *
     * Defines a one-to-many relationship linking this department to its employees.
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
