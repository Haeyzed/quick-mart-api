<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExpenseCategory;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * ExpenseCategoryService
 *
 * Handles all business logic for expense category operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class ExpenseCategoryService extends BaseService
{
    /**
     * Get paginated list of expense categories with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<ExpenseCategory>
     */
    public function getExpenseCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return ExpenseCategory::query()
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
     * Get a single expense category by ID.
     *
     * @param int $id Expense Category ID
     * @return ExpenseCategory
     */
    public function getExpenseCategory(int $id): ExpenseCategory
    {
        return ExpenseCategory::findOrFail($id);
    }

    /**
     * Create a new expense category.
     *
     * @param array<string, mixed> $data Validated expense category data
     * @return ExpenseCategory
     */
    public function createExpenseCategory(array $data): ExpenseCategory
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeExpenseCategoryData($data);
            return ExpenseCategory::create($data);
        });
    }

    /**
     * Normalize expense category data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeExpenseCategoryData(array $data): array
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
     * Update an existing expense category.
     *
     * @param ExpenseCategory $expenseCategory Expense Category instance to update
     * @param array<string, mixed> $data Validated expense category data
     * @return ExpenseCategory
     */
    public function updateExpenseCategory(ExpenseCategory $expenseCategory, array $data): ExpenseCategory
    {
        return $this->transaction(function () use ($expenseCategory, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeExpenseCategoryData($data);
            $expenseCategory->update($data);
            return $expenseCategory->fresh();
        });
    }

    /**
     * Bulk delete multiple expense categories.
     *
     * @param array<int> $ids Array of expense category IDs to delete
     * @return int Number of expense categories deleted
     */
    public function bulkDeleteExpenseCategories(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $expenseCategory = ExpenseCategory::findOrFail($id);
                $this->deleteExpenseCategory($expenseCategory);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete expense category {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single expense category.
     *
     * @param ExpenseCategory $expenseCategory Expense Category instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteExpenseCategory(ExpenseCategory $expenseCategory): bool
    {
        return $this->transaction(function () use ($expenseCategory) {
            if ($expenseCategory->expenses()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete expense category: category has associated expenses');
            }

            return $expenseCategory->delete();
        });
    }
}

