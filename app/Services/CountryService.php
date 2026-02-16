<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Country;
use App\Models\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class CountryService
 * Handles business logic for Countries (World reference data).
 */
class CountryService
{
    /**
     * Get paginated countries.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCountries(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Country::query()
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('iso2', 'like', '%'.$filters['search'].'%')
                    ->orWhere('iso3', 'like', '%'.$filters['search'].'%')
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of country options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Country::query()
            ->select('id', 'name', 'iso2')
            ->orderBy('name')
            ->get()
            ->map(fn (Country $country) => [
                'value' => $country->id,
                'label' => $country->name,
                'iso2' => $country->iso2,
            ]);
    }

    /**
     * Get states for a given country.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, State>
     */
    public function getStatesByCountry(Country $country): \Illuminate\Database\Eloquent\Collection
    {
        return $country->states()->orderBy('name')->get();
    }
}
