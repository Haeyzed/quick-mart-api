<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as SpatieRole;

/**
 * Role Model
 * 
 * Extends Spatie Permission Role with description, module, and is_active.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $guard_name
 * @property bool $is_active
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Role active()
 * @method static Builder<static>|Role filter(array $filters)
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Role customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Role last30Days(string $column = 'created_at')
 * @method static Builder<static>|Role last7Days(string $column = 'created_at')
 * @method static Builder<static>|Role lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Role lastYear(string $column = 'created_at')
 * @method static Builder<static>|Role monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Role newModelQuery()
 * @method static Builder<static>|Role newQuery()
 * @method static Builder<static>|Role onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role permission($permissions, $without = false)
 * @method static Builder<static>|Role quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Role query()
 * @method static Builder<static>|Role today(string $column = 'created_at')
 * @method static Builder<static>|Role whereCreatedAt($value)
 * @method static Builder<static>|Role whereDeletedAt($value)
 * @method static Builder<static>|Role whereDescription($value)
 * @method static Builder<static>|Role whereGuardName($value)
 * @method static Builder<static>|Role whereId($value)
 * @method static Builder<static>|Role whereIsActive($value)
 * @method static Builder<static>|Role whereName($value)
 * @method static Builder<static>|Role whereUpdatedAt($value)
 * @method static Builder<static>|Role withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutPermission($permissions)
 * @method static Builder<static>|Role withoutTrashed()
 * @method static Builder<static>|Role yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Role yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Role extends SpatieRole
{
    use HasFactory, FilterableByDates, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
        'is_active',
    ];

    /**
     * Scope a query to apply filters.
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
     * Scope a query to only include active roles.
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
