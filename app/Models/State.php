<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\State as WorldState;

/**
 * Class State
 * 
 * Represents a state/region from World reference data. Extends Nnjeim\World State.
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property int|null $country_id
 * @property string|null $country_code
 * @property string|null $state_code
 * @property string|null $type
 * @property string|null $latitude
 * @property string|null $longitude
 * @method static Builder|State newModelQuery()
 * @method static Builder|State newQuery()
 * @method static Builder|State query()
 * @method static Builder|State filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Country|null $country
 * @method static Builder<static>|State customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|State last30Days(string $column = 'created_at')
 * @method static Builder<static>|State last7Days(string $column = 'created_at')
 * @method static Builder<static>|State lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|State lastYear(string $column = 'created_at')
 * @method static Builder<static>|State monthToDate(string $column = 'created_at')
 * @method static Builder<static>|State quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|State today(string $column = 'created_at')
 * @method static Builder<static>|State whereCountryCode($value)
 * @method static Builder<static>|State whereCountryId($value)
 * @method static Builder<static>|State whereId($value)
 * @method static Builder<static>|State whereLatitude($value)
 * @method static Builder<static>|State whereLongitude($value)
 * @method static Builder<static>|State whereName($value)
 * @method static Builder<static>|State whereStateCode($value)
 * @method static Builder<static>|State whereType($value)
 * @method static Builder<static>|State yearToDate(string $column = 'created_at')
 * @method static Builder<static>|State yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class State extends WorldState
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'country_id',
        'country_code',
        'state_code',
        'type',
        'latitude',
        'longitude',
    ];

    /**
     * Scope a query to apply filters.
     *
     * @param array<string, mixed> $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('state_code', 'like', '%'.$filters['search'].'%')
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
