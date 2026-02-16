<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class CityService
 * Handles business logic for Cities (World reference data).
 */
class CityService
{
    /**
     * Get paginated cities.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCities(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return City::query()
            ->with(['country:id,name,iso2', 'state:id,name,state_code'])
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of city options (value/label format).
     */
    public function getOptions(): Collection
    {
        return City::query()
            ->select('id', 'name', 'state_id', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (City $city) => [
                'value' => $city->id,
                'label' => $city->name,
                'state_id' => $city->state_id,
                'country_id' => $city->country_id,
            ]);
    }
}
