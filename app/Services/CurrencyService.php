<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Currency;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * CurrencyService
 *
 * Handles all business logic for currency operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class CurrencyService extends BaseService
{
    /**
     * Get paginated list of currencies with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Currency>
     */
    public function getCurrencies(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Currency::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('symbol', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single currency by ID.
     *
     * @param int $id Currency ID
     * @return Currency
     */
    public function getCurrency(int $id): Currency
    {
        return Currency::findOrFail($id);
    }

    /**
     * Create a new currency.
     *
     * @param array<string, mixed> $data Validated currency data
     * @return Currency
     */
    public function createCurrency(array $data): Currency
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeCurrencyData($data);
            return Currency::create($data);
        });
    }

    /**
     * Normalize currency data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeCurrencyData(array $data): array
    {
        // is_active is stored as boolean (true/false)
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing currency.
     *
     * @param Currency $currency Currency instance to update
     * @param array<string, mixed> $data Validated currency data
     * @return Currency
     */
    public function updateCurrency(Currency $currency, array $data): Currency
    {
        return $this->transaction(function () use ($currency, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeCurrencyData($data);
            $currency->update($data);
            return $currency->fresh();
        });
    }

    /**
     * Bulk delete multiple currencies.
     *
     * @param array<int> $ids Array of currency IDs to delete
     * @return int Number of currencies deleted
     */
    public function bulkDeleteCurrencies(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $currency = Currency::findOrFail($id);
                $this->deleteCurrency($currency);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete currency {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single currency.
     *
     * @param Currency $currency Currency instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteCurrency(Currency $currency): bool
    {
        return $this->transaction(function () use ($currency) {
            if ($currency->sales()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete currency: currency has associated sales');
            }

            if ($currency->purchases()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete currency: currency has associated purchases');
            }

            if ($currency->payments()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete currency: currency has associated payments');
            }

            return $currency->delete();
        });
    }
}

