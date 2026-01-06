<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Department;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * DepartmentService
 *
 * Handles all business logic for department operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class DepartmentService extends BaseService
{
    /**
     * Get paginated list of departments with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Department>
     */
    public function getDepartments(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Department::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('name', 'like', '%' . $filters['search'] . '%')
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single department by ID.
     *
     * @param int $id Department ID
     * @return Department
     */
    public function getDepartment(int $id): Department
    {
        return Department::findOrFail($id);
    }

    /**
     * Create a new department.
     *
     * @param array<string, mixed> $data Validated department data
     * @return Department
     */
    public function createDepartment(array $data): Department
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeDepartmentData($data);
            return Department::create($data);
        });
    }

    /**
     * Normalize department data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDepartmentData(array $data): array
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
     * Update an existing department.
     *
     * @param Department $department Department instance to update
     * @param array<string, mixed> $data Validated department data
     * @return Department
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        return $this->transaction(function () use ($department, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeDepartmentData($data);
            $department->update($data);
            return $department->fresh();
        });
    }

    /**
     * Bulk delete multiple departments.
     *
     * @param array<int> $ids Array of department IDs to delete
     * @return int Number of departments deleted
     */
    public function bulkDeleteDepartments(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $department = Department::findOrFail($id);
                $this->deleteDepartment($department);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete department {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single department.
     *
     * @param Department $department Department instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteDepartment(Department $department): bool
    {
        return $this->transaction(function () use ($department) {
            if ($department->employees()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete department: department has associated employees');
            }

            return $department->delete();
        });
    }
}

