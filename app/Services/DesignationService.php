<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Designation;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * DesignationService
 *
 * Handles all business logic for designation operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class DesignationService extends BaseService
{
    /**
     * Get paginated list of designations with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Designation>
     */
    public function getDesignations(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Designation::query()
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
     * Get a single designation by ID.
     *
     * @param int $id Designation ID
     * @return Designation
     */
    public function getDesignation(int $id): Designation
    {
        return Designation::findOrFail($id);
    }

    /**
     * Create a new designation.
     *
     * @param array<string, mixed> $data Validated designation data
     * @return Designation
     */
    public function createDesignation(array $data): Designation
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeDesignationData($data);
            return Designation::create($data);
        });
    }

    /**
     * Normalize designation data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDesignationData(array $data): array
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
     * Update an existing designation.
     *
     * @param Designation $designation Designation instance to update
     * @param array<string, mixed> $data Validated designation data
     * @return Designation
     */
    public function updateDesignation(Designation $designation, array $data): Designation
    {
        return $this->transaction(function () use ($designation, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeDesignationData($data);
            $designation->update($data);
            return $designation->fresh();
        });
    }

    /**
     * Bulk delete multiple designations.
     *
     * @param array<int> $ids Array of designation IDs to delete
     * @return int Number of designations deleted
     */
    public function bulkDeleteDesignations(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $designation = Designation::findOrFail($id);
                $this->deleteDesignation($designation);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete designation {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single designation.
     *
     * @param Designation $designation Designation instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteDesignation(Designation $designation): bool
    {
        return $this->transaction(function () use ($designation) {
            if ($designation->employees()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete designation: designation has associated employees');
            }

            return $designation->delete();
        });
    }
}

