<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class IdCardTemplate
 *
 * Represents an ID card design template within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for ID card template entities.
 *
 * @property int $id
 * @property string $name
 * @property array $design_config
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|IdCardTemplate newModelQuery()
 * @method static Builder|IdCardTemplate newQuery()
 * @method static Builder|IdCardTemplate query()
 * @method static Builder|IdCardTemplate active()
 * @method static Builder|IdCardTemplate filter(array $filters)
 *
 * @method static Builder<static>|IdCardTemplate customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate last30Days(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate last7Days(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate lastYear(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate monthToDate(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate onlyTrashed()
 * @method static Builder<static>|IdCardTemplate quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate today(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate whereCreatedAt($value)
 * @method static Builder<static>|IdCardTemplate whereDeletedAt($value)
 * @method static Builder<static>|IdCardTemplate whereDesignConfig($value)
 * @method static Builder<static>|IdCardTemplate whereId($value)
 * @method static Builder<static>|IdCardTemplate whereIsActive($value)
 * @method static Builder<static>|IdCardTemplate whereName($value)
 * @method static Builder<static>|IdCardTemplate whereUpdatedAt($value)
 * @method static Builder<static>|IdCardTemplate withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|IdCardTemplate withoutTrashed()
 * @method static Builder<static>|IdCardTemplate yearToDate(string $column = 'created_at')
 * @method static Builder<static>|IdCardTemplate yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class IdCardTemplate extends Model
{
    use FilterableByDates, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'design_config',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'design_config' => 'array',
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
     * Scope a query to only include active templates.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
