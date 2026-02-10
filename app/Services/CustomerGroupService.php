<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\CustomerGroupsImport;
use App\Models\CustomerGroup;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

/**
 * CustomerGroupService
 *
 * Handles all business logic for customer group operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class CustomerGroupService extends BaseService
{
    /**
     * Get paginated list of customer groups with optional filters.
     *
     * @param  array<string, mixed>  $filters  Available filters: is_active, search
     * @param  int  $perPage  Number of items per page (default: 10)
     * @return LengthAwarePaginator<CustomerGroup>
     */
    public function getCustomerGroups(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return CustomerGroup::query()
            ->when(
                isset($filters['is_active']),
                fn ($query) => $query->where('is_active', (bool) $filters['is_active'])
            )
            ->when(
                ! empty($filters['search'] ?? null),
                fn ($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['search'].'%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single customer group by ID.
     *
     * @param  int  $id  Customer Group ID
     */
    public function getCustomerGroup(int $id): CustomerGroup
    {
        return CustomerGroup::findOrFail($id);
    }

    /**
     * Create a new customer group.
     *
     * @param  array<string, mixed>  $data  Validated customer group data
     */
    public function createCustomerGroup(array $data): CustomerGroup
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeCustomerGroupData($data);

            return CustomerGroup::create($data);
        });
    }

    /**
     * Normalize customer group data to match database schema requirements.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCustomerGroupData(array $data): array
    {
        // is_active is stored as boolean (true/false)
        if (! isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool) filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing customer group.
     *
     * @param  CustomerGroup  $customerGroup  Customer Group instance to update
     * @param  array<string, mixed>  $data  Validated customer group data
     */
    public function updateCustomerGroup(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        return $this->transaction(function () use ($customerGroup, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeCustomerGroupData($data);
            $customerGroup->update($data);

            return $customerGroup->fresh();
        });
    }

    /**
     * Bulk delete multiple customer groups.
     *
     * @param  array<int>  $ids  Array of customer group IDs to delete
     * @return int Number of customer groups deleted
     */
    public function bulkDeleteCustomerGroups(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $customerGroup = CustomerGroup::findOrFail($id);
                $this->deleteCustomerGroup($customerGroup);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete customer group {$id}: ".$e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single customer group.
     *
     * @param  CustomerGroup  $customerGroup  Customer Group instance to delete
     *
     * @throws HttpResponseException
     */
    public function deleteCustomerGroup(CustomerGroup $customerGroup): bool
    {
        return $this->transaction(function () use ($customerGroup) {
            if ($customerGroup->customers()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete customer group: customer group has associated customers');
            }

            return $customerGroup->delete();
        });
    }

    /**
     * Import customer groups from a file.
     */
    public function importCustomerGroups(UploadedFile $file): void
    {
        $this->transaction(function () use ($file) {
            Excel::import(new CustomerGroupsImport, $file);
        });
    }

    /**
     * Get all active customer groups.
     *
     * @return Collection<int, CustomerGroup>
     */
    public function getAllActive(): Collection
    {
        return CustomerGroup::where('is_active', true)->get();
    }
}
