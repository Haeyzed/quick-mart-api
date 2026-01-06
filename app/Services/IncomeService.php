<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CashRegister;
use App\Models\Income;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

/**
 * IncomeService
 *
 * Handles all business logic for income operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class IncomeService extends BaseService
{
    /**
     * Get paginated list of incomes with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: starting_date, ending_date, warehouse_id, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Income>
     */
    public function getIncomes(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Income::with(['warehouse', 'incomeCategory', 'account', 'user']);

        // Date filtering
        if (!empty($filters['starting_date'])) {
            $query->whereDate('created_at', '>=', $filters['starting_date']);
        }

        if (!empty($filters['ending_date'])) {
            $query->whereDate('created_at', '<=', $filters['ending_date']);
        }

        // Warehouse filtering
        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        // Search filtering
        if (!empty($filters['search'] ?? null)) {
            $query->where(function ($q) use ($filters) {
                $q->where('reference_no', 'like', '%' . $filters['search'] . '%')
                    ->orWhereDate('created_at', date('Y-m-d', strtotime(str_replace('/', '-', $filters['search']))));
            });
        }

        // Staff access check (if user role_id > 2, only show their own incomes)
        $user = Auth::user();
        if ($user && $user->role_id > 2) {
            $query->where('user_id', $user->id);
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    /**
     * Get a single income by ID.
     *
     * @param int $id Income ID
     * @return Income
     */
    public function getIncome(int $id): Income
    {
        return Income::with(['warehouse', 'incomeCategory', 'account', 'user', 'cashRegister'])
            ->findOrFail($id);
    }

    /**
     * Create a new income.
     *
     * @param array<string, mixed> $data Validated income data
     * @return Income
     */
    public function createIncome(array $data): Income
    {
        return $this->transaction(function () use ($data) {
            // Auto-generate reference_no if not provided
            if (empty($data['reference_no'])) {
                $data['reference_no'] = 'ir-' . date('Ymd') . '-' . date('His');
            }

            // Set created_at default if not provided
            if (!isset($data['created_at'])) {
                $data['created_at'] = now();
            }

            // Set user_id if not provided (business logic - Auth::id())
            if (!isset($data['user_id'])) {
                $data['user_id'] = Auth::id();
            }

            // Find cash register if user_id and warehouse_id are set (business logic)
            if (isset($data['user_id']) && isset($data['warehouse_id'])) {
                $cashRegister = CashRegister::where([
                    ['user_id', $data['user_id']],
                    ['warehouse_id', $data['warehouse_id']],
                    ['status', true]
                ])->first();

                if ($cashRegister) {
                    $data['cash_register_id'] = $cashRegister->id;
                }
            }

            return Income::create($data);
        });
    }

    /**
     * Update an existing income.
     *
     * @param Income $income Income instance to update
     * @param array<string, mixed> $data Validated income data
     * @return Income
     */
    public function updateIncome(Income $income, array $data): Income
    {
        return $this->transaction(function () use ($income, $data) {
            $income->update($data);
            return $income->fresh();
        });
    }

    /**
     * Bulk delete multiple incomes.
     *
     * @param array<int> $ids Array of income IDs to delete
     * @return int Number of incomes deleted
     */
    public function bulkDeleteIncomes(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $income = Income::findOrFail($id);
                $this->deleteIncome($income);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete income {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single income.
     *
     * @param Income $income Income instance to delete
     * @return bool
     */
    public function deleteIncome(Income $income): bool
    {
        return $this->transaction(function () use ($income) {
            return $income->delete();
        });
    }
}

