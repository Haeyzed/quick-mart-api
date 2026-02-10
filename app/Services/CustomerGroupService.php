<?php

declare(strict_types=1);

namespace App\Services;

use App\Imports\CustomerGroupsImport;
use App\Models\CustomerGroup;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Service class for Customer Group entity lifecycle operations.
 *
 * Centralizes business logic for Customer Group CRUD, bulk actions, and imports.
 * Delegates permission checks to CheckPermissionsTrait.
 */
class CustomerGroupService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Retrieve a single customer group by instance.
     *
     * Requires customer-groups-index permission. Use for show/display operations.
     *
     * @param  CustomerGroup  $customerGroup  The customer group instance to retrieve.
     * @return CustomerGroup The refreshed customer group instance.
     */
    public function getCustomerGroup(CustomerGroup $customerGroup): CustomerGroup
    {
        $this->requirePermission('customer-groups-index');

        return $customerGroup->fresh();
    }

    /**
     * Retrieve customer groups with optional filters and pagination.
     *
     * Supports filtering by status (active/inactive) and search term.
     * Requires customer-groups-index permission.
     *
     * @param  array<string, mixed>  $filters  Associative array with optional keys: 'status', 'search'.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<CustomerGroup> Paginated customer group collection.
     */
    public function getCustomerGroups(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customer-groups-index');

        return CustomerGroup::query()
            ->when(isset($filters['status']), fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(! empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where('name', 'like', $term);
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Create a new customer group.
     *
     * Requires customer-groups-create permission.
     *
     * @param  array<string, mixed>  $data  Validated customer group attributes.
     * @return CustomerGroup The created customer group instance.
     */
    public function createCustomerGroup(array $data): CustomerGroup
    {
        $this->requirePermission('customer-groups-create');

        return DB::transaction(function () use ($data) {
            $data = $this->normalizeCustomerGroupData($data);

            return CustomerGroup::create($data);
        });
    }

    /**
     * Update an existing customer group.
     *
     * Requires customer-groups-update permission.
     *
     * @param  CustomerGroup  $customerGroup  The customer group instance to update.
     * @param  array<string, mixed>  $data  Validated customer group attributes.
     * @return CustomerGroup The updated customer group (refreshed).
     */
    public function updateCustomerGroup(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        $this->requirePermission('customer-groups-update');

        return DB::transaction(function () use ($customerGroup, $data) {
            $data = $this->normalizeCustomerGroupData($data);
            $customerGroup->update($data);

            return $customerGroup->fresh();
        });
    }

    /**
     * Delete a customer group.
     *
     * Fails with 422 if the group has associated customers.
     * Requires customer-groups-delete permission.
     *
     * @param  CustomerGroup  $customerGroup  The customer group instance to delete.
     */
    public function deleteCustomerGroup(CustomerGroup $customerGroup): void
    {
        $this->requirePermission('customer-groups-delete');

        DB::transaction(function () use ($customerGroup) {
            if ($customerGroup->customers()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete customer group: group has associated customers.');
            }
            $customerGroup->delete();
        });
    }

    /**
     * Bulk delete customer groups.
     *
     * Skips non-existent IDs. Fails if any group has associated customers.
     * Requires customer-groups-delete permission.
     *
     * @param  array<int>  $ids  Customer group IDs to delete.
     * @return int Number of customer groups successfully deleted.
     */
    public function bulkDeleteCustomerGroups(array $ids): int
    {
        $this->requirePermission('customer-groups-delete');

        return DB::transaction(function () use ($ids) {
            $customerGroups = CustomerGroup::whereIn('id', $ids)->get();
            $deletedCount = 0;

            foreach ($customerGroups as $customerGroup) {
                $this->deleteCustomerGroup($customerGroup);
                $deletedCount++;
            }

            return $deletedCount;
        });
    }

    /**
     * Bulk activate customer groups by ID.
     *
     * Sets is_active to true for all matching customer groups. Requires customer-groups-update permission.
     *
     * @param  array<int>  $ids  Customer group IDs to activate.
     * @return int Number of customer groups updated.
     */
    public function bulkActivateCustomerGroups(array $ids): int
    {
        $this->requirePermission('customer-groups-update');

        return CustomerGroup::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate customer groups by ID.
     *
     * Sets is_active to false for all matching customer groups. Requires customer-groups-update permission.
     *
     * @param  array<int>  $ids  Customer group IDs to deactivate.
     * @return int Number of customer groups updated.
     */
    public function bulkDeactivateCustomerGroups(array $ids): int
    {
        $this->requirePermission('customer-groups-update');

        return CustomerGroup::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Import customer groups from an Excel or CSV file.
     *
     * Requires customer-groups-import permission.
     *
     * @param  UploadedFile  $file  The uploaded import file.
     */
    public function importCustomerGroups(UploadedFile $file): void
    {
        $this->requirePermission('customer-groups-import');

        Excel::import(new CustomerGroupsImport, $file);
    }

    /**
     * Get all active customer groups.
     *
     * Requires customer-groups-index permission.
     *
     * @return Collection<int, CustomerGroup>
     */
    public function getAllActive(): Collection
    {
        $this->requirePermission('customer-groups-index');

        return CustomerGroup::where('is_active', true)->orderBy('name')->get();
    }

    /**
     * Normalize customer group data to match database schema requirements.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeCustomerGroupData(array $data): array
    {
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
}
