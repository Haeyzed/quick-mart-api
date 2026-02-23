<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\Timezone as WorldTimezone;

/**
 * Class Timezone
 * 
 * Represents a timezone from World reference data. Extends Nnjeim\World Timezone.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @method static Builder|Timezone newModelQuery()
 * @method static Builder|Timezone newQuery()
 * @method static Builder|Timezone query()
 * @method static Builder|Timezone filter(array $filters)
 * @property-read \App\Models\Country|null $country
 * @method static Builder<static>|Timezone customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Timezone last30Days(string $column = 'created_at')
 * @method static Builder<static>|Timezone last7Days(string $column = 'created_at')
 * @method static Builder<static>|Timezone lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Timezone lastYear(string $column = 'created_at')
 * @method static Builder<static>|Timezone monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Timezone quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Timezone today(string $column = 'created_at')
 * @method static Builder<static>|Timezone whereCountryId($value)
 * @method static Builder<static>|Timezone whereId($value)
 * @method static Builder<static>|Timezone whereName($value)
 * @method static Builder<static>|Timezone yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Timezone yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Timezone extends WorldTimezone
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'name',
    ];

    /**
     * Scope a query to apply filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
            )
            ->when(
                ! empty($filters['country_id']),
                fn ($q) => $q->where('country_id', $filters['country_id'])
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}
