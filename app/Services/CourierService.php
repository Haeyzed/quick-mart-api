<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Courier;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * CourierService
 *
 * Handles all business logic for courier operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class CourierService extends BaseService
{
    /**
     * Get paginated list of couriers with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Courier>
     */
    public function getCouriers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Courier::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('phone_number', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('address', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single courier by ID.
     *
     * @param int $id Courier ID
     * @return Courier
     */
    public function getCourier(int $id): Courier
    {
        return Courier::findOrFail($id);
    }

    /**
     * Create a new courier.
     *
     * @param array<string, mixed> $data Validated courier data
     * @return Courier
     */
    public function createCourier(array $data): Courier
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeCourierData($data);
            return Courier::create($data);
        });
    }

    /**
     * Normalize courier data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeCourierData(array $data): array
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
     * Update an existing courier.
     *
     * @param Courier $courier Courier instance to update
     * @param array<string, mixed> $data Validated courier data
     * @return Courier
     */
    public function updateCourier(Courier $courier, array $data): Courier
    {
        return $this->transaction(function () use ($courier, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeCourierData($data);
            $courier->update($data);
            return $courier->fresh();
        });
    }

    /**
     * Bulk delete multiple couriers.
     *
     * @param array<int> $ids Array of courier IDs to delete
     * @return int Number of couriers deleted
     */
    public function bulkDeleteCouriers(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $courier = Courier::findOrFail($id);
                $this->deleteCourier($courier);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete courier {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single courier.
     *
     * @param Courier $courier Courier instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteCourier(Courier $courier): bool
    {
        return $this->transaction(function () use ($courier) {
            if ($courier->deliveries()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete courier: courier has associated deliveries');
            }

            return $courier->delete();
        });
    }
}

