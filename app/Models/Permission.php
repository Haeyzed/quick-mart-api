<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Custom Permission model extending Spatie with module and is_active support.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property bool $is_active
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Permission active()
 * @method static Builder<static>|Permission filter(array $filters)
 * @property string|null $description
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Permission customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Permission last30Days(string $column = 'created_at')
 * @method static Builder<static>|Permission last7Days(string $column = 'created_at')
 * @method static Builder<static>|Permission lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Permission lastYear(string $column = 'created_at')
 * @method static Builder<static>|Permission monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Permission newModelQuery()
 * @method static Builder<static>|Permission newQuery()
 * @method static Builder<static>|Permission onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission permission($permissions, $without = false)
 * @method static Builder<static>|Permission quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission role($roles, $guard = null, $without = false)
 * @method static Builder<static>|Permission today(string $column = 'created_at')
 * @method static Builder<static>|Permission whereCreatedAt($value)
 * @method static Builder<static>|Permission whereDeletedAt($value)
 * @method static Builder<static>|Permission whereDescription($value)
 * @method static Builder<static>|Permission whereGuardName($value)
 * @method static Builder<static>|Permission whereId($value)
 * @method static Builder<static>|Permission whereIsActive($value)
 * @method static Builder<static>|Permission whereModule($value)
 * @method static Builder<static>|Permission whereName($value)
 * @method static Builder<static>|Permission whereUpdatedAt($value)
 * @method static Builder<static>|Permission withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutRole($roles, $guard = null)
 * @method static Builder<static>|Permission withoutTrashed()
 * @method static Builder<static>|Permission yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Permission yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Permission extends SpatiePermission
{
    use HasFactory, FilterableByDates, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'module',
        'is_active',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param Builder $query
     * @param array<string, mixed> $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = '%' . $filters['search'] . '%';
                    $q->where(fn(Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('module', 'like', $term)
                        ->orWhere('description', 'like', $term)
                    );
                }
            )
            ->when(
                !empty($filters['guard_name']),
                fn(Builder $q) => $q->where('guard_name', $filters['guard_name'])
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active permissions.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
