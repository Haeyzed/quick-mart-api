<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\IncomeCategory;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * IncomeCategoryService
 *
 * Handles all business logic for income category operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class IncomeCategoryService extends BaseService
{
    /**
     * Get paginated list of income categories with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<IncomeCategory>
     */
    public function getIncomeCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return IncomeCategory::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('code', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single income category by ID.
     *
     * @param int $id Income Category ID
     * @return IncomeCategory
     */
    public function getIncomeCategory(int $id): IncomeCategory
    {
        return IncomeCategory::findOrFail($id);
    }

    /**
     * Create a new income category.
     *
     * @param array<string, mixed> $data Validated income category data
     * @return IncomeCategory
     */
    public function createIncomeCategory(array $data): IncomeCategory
    {
        return $this->transaction(function () use ($data) {
            // Auto-generate code if not provided
            if (empty($data['code'])) {
                $data['code'] = IncomeCategory::generateCode();
            }

            // Normalize data to match database schema
            $data = $this->normalizeIncomeCategoryData($data);
            return IncomeCategory::create($data);
        });
    }

    /**
     * Normalize income category data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeIncomeCategoryData(array $data): array
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
     * Update an existing income category.
     *
     * @param IncomeCategory $incomeCategory Income Category instance to update
     * @param array<string, mixed> $data Validated income category data
     * @return IncomeCategory
     */
    public function updateIncomeCategory(IncomeCategory $incomeCategory, array $data): IncomeCategory
    {
        return $this->transaction(function () use ($incomeCategory, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeIncomeCategoryData($data);
            $incomeCategory->update($data);
            return $incomeCategory->fresh();
        });
    }

    /**
     * Bulk delete multiple income categories.
     *
     * @param array<int> $ids Array of income category IDs to delete
     * @return int Number of income categories deleted
     */
    public function bulkDeleteIncomeCategories(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $incomeCategory = IncomeCategory::findOrFail($id);
                $this->deleteIncomeCategory($incomeCategory);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete income category {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single income category.
     *
     * @param IncomeCategory $incomeCategory Income Category instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteIncomeCategory(IncomeCategory $incomeCategory): bool
    {
        return $this->transaction(function () use ($incomeCategory) {
            if ($incomeCategory->incomes()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete income category: category has associated incomes');
            }

            // Set is_active = false instead of deleting (matching old controller)
            $incomeCategory->is_active = false;
            $incomeCategory->save();
            return $incomeCategory->delete(); // Soft delete
        });
    }
}

