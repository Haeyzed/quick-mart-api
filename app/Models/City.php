<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\City as WorldCity;

/**
 * Class City
 *
 * Represents a city from World reference data. Extends Nnjeim\World City.
 *
 * @property int $id
 * @property int $country_id
 * @property int $state_id
 * @property string $name
 * @property string $country_code
 * @property string|null $state_code
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @method static Builder|City newModelQuery()
 * @method static Builder|City newQuery()
 * @method static Builder|City query()
 * @method static Builder|City filter(array $filters)
 */
class City extends WorldCity
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'country_code',
        'state_code',
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
            )
            ->when(
                ! empty($filters['country_id']),
                fn ($q) => $q->where('country_id', $filters['country_id'])
            )
            ->when(
                ! empty($filters['state_id']),
                fn ($q) => $q->where('state_id', $filters['state_id'])
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}
