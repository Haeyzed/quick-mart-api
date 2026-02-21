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
 *
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country filter(array $filters)
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
