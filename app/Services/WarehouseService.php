<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\WarehousesImport;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\User;
use App\Models\Warehouse;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

/**
 * WarehouseService
 *
 * Handles all business logic for warehouse operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class WarehouseService extends BaseService
{
    /**
     * Get paginated list of warehouses with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Warehouse>
     */
    public function getWarehouses(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Warehouse::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single warehouse by ID.
     *
     * @param int $id Warehouse ID
     * @return Warehouse
     */
    public function getWarehouse(int $id): Warehouse
    {
        return Warehouse::findOrFail($id);
    }

    /**
     * Create a new warehouse.
     *
     * @param array<string, mixed> $data Validated warehouse data
     * @return Warehouse
     */
    public function createWarehouse(array $data): Warehouse
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeWarehouseData($data);

            $warehouse = Warehouse::create($data);

            // Create ProductWarehouse entries for all existing products
            $productIds = Product::pluck('id');
            foreach ($productIds as $productId) {
                ProductWarehouse::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id,
                    'qty' => 0,
                ]);
            }

            return $warehouse;
        });
    }

    /**
     * Normalize warehouse data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeWarehouseData(array $data): array
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
     * Update an existing warehouse.
     *
     * @param Warehouse $warehouse Warehouse instance to update
     * @param array<string, mixed> $data Validated warehouse data
     * @return Warehouse
     */
    public function updateWarehouse(Warehouse $warehouse, array $data): Warehouse
    {
        return $this->transaction(function () use ($warehouse, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeWarehouseData($data);
            $warehouse->update($data);
            return $warehouse->fresh();
        });
    }

    /**
     * Bulk delete multiple warehouses.
     *
     * @param array<int> $ids Array of warehouse IDs to delete
     * @return int Number of warehouses deleted
     */
    public function bulkDeleteWarehouses(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $warehouse = Warehouse::findOrFail($id);
                $this->deleteWarehouse($warehouse);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete warehouse {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single warehouse.
     *
     * @param Warehouse $warehouse Warehouse instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteWarehouse(Warehouse $warehouse): bool
    {
        return $this->transaction(function () use ($warehouse) {
            if ($warehouse->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete warehouse: warehouse has associated products');
            }

            if ($warehouse->sales()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete warehouse: warehouse has associated sales');
            }

            if ($warehouse->purchases()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete warehouse: warehouse has associated purchases');
            }

            return $warehouse->delete();
        });
    }

    /**
     * Import warehouses from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importWarehouses(UploadedFile $file): void
    {
        $this->transaction(function () use ($file) {
            Excel::import(new WarehousesImport(), $file);
        });
    }

    /**
     * Get all active warehouses.
     * Returns warehouses based on user role - users with role_id > 2 only see their assigned warehouse.
     *
     * @param User|null $user Optional user instance (defaults to authenticated user)
     * @return Collection<int, Warehouse>
     */
    public function getAllActive(?User $user = null): Collection
    {
        $user = $user ?? Auth::user();

        if ($user && $user->role_id > 2 && $user->warehouse_id) {
            return Warehouse::where('is_active', true)
                ->where('id', $user->warehouse_id)
                ->get();
        }

        return Warehouse::where('is_active', true)->get();
    }
}

