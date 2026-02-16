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
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of timezone options grouped by region (value/label format).
     *
     * @return array<int, array{region: string, options: array<int, array{value: int, label: string, country_id: int|null}>}>
     */
    public function getOptions(): array
    {
        $timezones = Timezone::query()
            ->select('id', 'name', 'country_id')
            ->orderBy('name')
            ->get();

        $grouped = $timezones->groupBy(function (Timezone $timezone): string {
            return str_contains($timezone->name, '/')
                ? explode('/', $timezone->name, 2)[0]
                : 'Other';
        });

        $grouped = $grouped->sortKeys();

        return $grouped->map(function (Collection $items, string $region): array {
            return [
                'region' => $region,
                'options' => $items->map(fn (Timezone $tz) => [
                    'value' => $tz->id,
                    'label' => $tz->name,
                    'country_id' => $tz->country_id,
                ])->values()->all(),
            ];
        })->values()->all();
    }
}
