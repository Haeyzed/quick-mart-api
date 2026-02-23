<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\Country as WorldCountry;

/**
 * Class Country
 * 
 * Represents a country from World reference data. Extends Nnjeim\World Country.
 *
 * @property int $id
 * @property string $iso2
 * @property string $name
 * @property int $status
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $native
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\State> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timezone> $timezones
 * @property-read int|null $timezones_count
 * @method static Builder<static>|Country customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Country last30Days(string $column = 'created_at')
 * @method static Builder<static>|Country last7Days(string $column = 'created_at')
 * @method static Builder<static>|Country lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Country lastYear(string $column = 'created_at')
 * @method static Builder<static>|Country monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Country quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Country today(string $column = 'created_at')
 * @method static Builder<static>|Country whereEmoji($value)
 * @method static Builder<static>|Country whereEmojiU($value)
 * @method static Builder<static>|Country whereId($value)
 * @method static Builder<static>|Country whereIso2($value)
 * @method static Builder<static>|Country whereIso3($value)
 * @method static Builder<static>|Country whereLatitude($value)
 * @method static Builder<static>|Country whereLongitude($value)
 * @method static Builder<static>|Country whereName($value)
 * @method static Builder<static>|Country whereNative($value)
 * @method static Builder<static>|Country wherePhoneCode($value)
 * @method static Builder<static>|Country whereRegion($value)
 * @method static Builder<static>|Country whereStatus($value)
 * @method static Builder<static>|Country whereSubregion($value)
 * @method static Builder<static>|Country yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Country yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Country extends WorldCountry
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iso2',
        'name',
        'status',
        'phone_code',
        'iso3',
        'region',
        'subregion',
        'native',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
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
                    ->orWhere('iso2', 'like', '%'.$filters['search'].'%')
                    ->orWhere('iso3', 'like', '%'.$filters['search'].'%')
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}
