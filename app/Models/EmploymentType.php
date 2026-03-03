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
 * Class EmploymentType
 *
 * Represents a type of employment (e.g. full-time, contract, intern). Handles the underlying data
 * structure, relationships, and specific query scopes for employment type entities.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|EmploymentType newModelQuery()
 * @method static Builder|EmploymentType newQuery()
 * @method static Builder|EmploymentType query()
 * @method static Builder|EmploymentType active()
 * @method static Builder|EmploymentType filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 *
 * @method static Builder<static>|EmploymentType customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmploymentType last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType onlyTrashed()
 * @method static Builder<static>|EmploymentType quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType today(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType whereCreatedAt($value)
 * @method static Builder<static>|EmploymentType whereDeletedAt($value)
 * @method static Builder<static>|EmploymentType whereId($value)
 * @method static Builder<static>|EmploymentType whereIsActive($value)
 * @method static Builder<static>|EmploymentType whereName($value)
 * @method static Builder<static>|EmploymentType whereUpdatedAt($value)
 * @method static Builder<static>|EmploymentType withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|EmploymentType withoutTrashed()
 * @method static Builder<static>|EmploymentType yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmploymentType yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmploymentType extends Model implements AuditableContract
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
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
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
     * Scope a query to only include active employment types.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the employees associated with this employment type.
     *
     * @return HasMany<Employee, self>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'employment_type_id');
    }
}
