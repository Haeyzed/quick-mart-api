<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\City;
use App\Models\State;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class StateService
 * Handles business logic for States (World reference data).
 */
class StateService
{
    /**
     * Get paginated states.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedStates(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return State::query()
            ->with('country:id,name,iso2')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of state options (value/label format).
     */
    public function getOptions(): Collection
    {
        return State::query()
            ->select('id', 'name', 'state_code', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (State $state) => [
                'value' => $state->id,
                'label' => $state->name,
                'state_code' => $state->state_code,
                'country_id' => $state->country_id,
            ]);
    }

    /**
     * Get city options (value/label) for a given state.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getCityOptionsByState(State $state): Collection
    {
        return $state->cities()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (City $city) => [
                'value' => $city->id,
                'label' => $city->name,
            ]);
    }
}
