<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class WorldCurrencyService
 * Handles business logic for World Currencies (World reference data).
 */
class WorldCurrencyService
{
    /**
     * Get paginated world currencies.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedWorldCurrencies(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Currency::query()
            ->with('country:id,name,iso2')
            ->when(
                ! empty($filters['search']),
                fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%')
            )
            ->when(
                ! empty($filters['country_id']),
                fn ($q) => $q->where('country_id', $filters['country_id'])
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of world currency options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Currency::query()
            ->select('id', 'name', 'code', 'symbol', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Currency $currency) => [
                'value' => $currency->id,
                'label' => $currency->name,
                'code' => $currency->code,
                'symbol' => $currency->symbol,
                'country_id' => $currency->country_id,
            ]);
    }
}
