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
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class CustomerGroupService
 * Handles business logic for Customer Groups.
 */
class CustomerGroupService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated customer groups based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCustomerGroups(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return CustomerGroup::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of customer group options (value/label format).
     *
     * @return Collection<int, array{value: int, label: string}>
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
     * Create a new customer group.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomerGroup(array $data): CustomerGroup
    {
        return DB::transaction(function () use ($data) {
            $data['is_active'] = $data['is_active'] ?? false;
            if (isset($data['is_active']) && ! is_bool($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }

            return CustomerGroup::query()->create($data);
        });
    }

    /**
     * Update an existing customer group.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCustomerGroup(CustomerGroup $customerGroup, array $data): CustomerGroup
    {
        return DB::transaction(function () use ($customerGroup, $data) {
            if (isset($data['is_active']) && ! is_bool($data['is_active'])) {
                $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
            }
            $customerGroup->update($data);

            return $customerGroup->fresh();
        });
    }

    /**
     * Delete a customer group.
     *
     * @throws UnprocessableEntityHttpException
     */
    public function deleteCustomerGroup(CustomerGroup $customerGroup): void
    {
        if ($customerGroup->customers()->exists()) {
            throw new UnprocessableEntityHttpException(
                'Cannot delete customer group: group has associated customers.'
            );
        }

        DB::transaction(fn () => $customerGroup->delete());
    }

    /**
     * Bulk delete customer groups.
     *
     * @param  array<int>  $ids
     * @return int Count of deleted items.
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
     * Update status for multiple customer groups.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return CustomerGroup::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import customer groups from file.
     */
    public function importCustomerGroups(UploadedFile $file): void
    {
        ExcelFacade::import(new CustomerGroupsImport, $file);
    }

    /**
     * Download a customer groups CSV template.
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
     * Export customer groups to file.
     *
     * @param  array<int>  $ids
     * @param  string  $format  'excel' or 'pdf'
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters
     * @return string Relative file path.
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
