<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Timezone;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class TimezoneService
 * Handles business logic for Timezones (World reference data).
 */
class TimezoneService
{
    /**
     * Get paginated timezones.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedTimezones(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Timezone::query()
            ->with('country:id,name,iso2')
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
            )
            ->when(
                ! empty($filters['country_id']),
                fn ($q) => $q->where('country_id', $filters['country_id'])
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of timezone options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Timezone::query()
            ->select('id', 'name', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Timezone $timezone) => [
                'value' => $timezone->id,
                'label' => $timezone->name,
                'country_id' => $timezone->country_id,
            ]);
    }
}
