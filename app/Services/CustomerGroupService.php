<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CustomerGroupsExport;
use App\Imports\CustomerGroupsImport;
use App\Models\CustomerGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class CustomerGroupService
 *
 * Handles all core business logic and database interactions for Customer Groups.
 * Acts as the intermediary between the controllers and the database layer.
 */
class CustomerGroupService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated customer groups based on provided filters.
     *
     * Retrieves a paginated list of customer groups, applying scopes for searching,
     * status filtering, and date ranges.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'status', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of CustomerGroup models.
     */
    public function getPaginatedCustomerGroups(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return CustomerGroup::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active customer group options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active groups.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return CustomerGroup::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (CustomerGroup $group) => [
                'value' => $group->id,
                'label' => $group->name,
            ]);
    }

    /**
     * Create a newly registered customer group.
     *
     * Stores the new customer group record within a database transaction.
     * Normalizes is_active to boolean when provided.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new customer group.
     * @return CustomerGroup The newly created CustomerGroup model instance.
     */
    public function createCustomerGroup(array $data): CustomerGroup
    {
        return DB::transaction(function () use ($data) {
            return CustomerGroup::query()->create($data);
        });
    }

    /**
     * Update an existing customer group's information.
     *
     * Updates the customer group record within a database transaction.
     * Normalizes is_active to boolean when provided.
     *
     * @param  CustomerGroup  $customerGroup  The customer group model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return CustomerGroup The freshly updated CustomerGroup model instance.
     */
    public function updateCustomerGroup(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        return DB::transaction(function () use ($customerGroup, $data) {
            $customerGroup->update($data);

            return $customerGroup->fresh();
        });
    }

    /**
     * Delete a specific customer group.
     *
     * Will abort if the customer group has any associated customers.
     *
     * @param  CustomerGroup  $customerGroup  The customer group model instance to delete.
     *
     * @throws UnprocessableEntityHttpException If the customer group has associated customers.
     */
    public function deleteCustomerGroup(CustomerGroup $customerGroup): void
    {
        if ($customerGroup->customers()->exists()) {
            throw new ConflictHttpException("Cannot delete customer group '{$customerGroup->name}' as it has associated customers.");
        }

        DB::transaction(fn () => $customerGroup->delete());
    }

    /**
     * Bulk delete multiple customer groups.
     *
     * Iterates over an array of customer group IDs and attempts to delete them.
     * Skips any groups that have associated customers.
     *
     * @param  array<int>  $ids  Array of customer group IDs to be deleted.
     * @return int The total count of successfully deleted customer groups.
     */
    public function bulkDeleteCustomerGroups(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $groups = CustomerGroup::query()->whereIn('id', $ids)->get();
            $count = 0;
            foreach ($groups as $group) {
                if ($group->customers()->exists()) {
                    continue;
                }
                $group->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple customer groups.
     *
     * @param  array<int>  $ids  Array of customer group IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return CustomerGroup::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple customer groups from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing customer group data.
     */
    public function importCustomerGroups(UploadedFile $file): void
    {
        ExcelFacade::import(new CustomerGroupsImport, $file);
    }

    /**
     * Retrieve the path to the sample customer groups import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     */
    public function download(): string
    {
        $fileName = 'customer-groups-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template customer groups not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing customer group data.
     *
     * Compiles the requested customer group data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific customer group IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'customer_groups_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CustomerGroupsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
