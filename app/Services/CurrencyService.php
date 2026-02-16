<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class CurrencyService
 * Handles business logic for Currencies (World reference data).
 */
class CurrencyService
{
    /**
     * Get paginated currencies.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCurrencies(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Currency::query()
            ->with('country:id,name,iso2')
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of currency options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Currency::query()
            ->select('id', 'name', 'country_id')
            ->orderBy('name')
            ->get()
            ->map(fn (Currency $currency) => [
                'value' => $currency->id,
                'label' => $currency->name,
                'country_id' => $currency->country_id,
            ]);
    }
}
