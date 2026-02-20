<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\TaxesExport;
use App\Imports\TaxesImport;
use App\Models\Tax;
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
 * Class TaxService
 * * Handles all core business logic and database interactions for Taxes.
 * Acts as the intermediary between the controllers and the database layer.
 */
class TaxService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * TaxService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated taxes based on provided filters.
     *
     * Retrieves a paginated list of taxes, applying scopes for searching,
     * status filtering, and date ranges.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'status', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Tax models.
     */
    public function getPaginatedTaxes(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Tax::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active tax options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active taxes.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Tax::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Tax $tax) => [
                'value' => $tax->id,
                'label' => $tax->name,
            ]);
    }

    /**
     * Create a newly registered tax.
     *
     * Stores the new tax record within a database transaction to ensure data integrity.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new tax.
     * @return Tax The newly created Tax model instance.
     */
    public function createTax(array $data): Tax
    {
        return DB::transaction(function () use ($data) {
            return Tax::query()->create($data);
        });
    }

    /**
     * Update an existing tax's information.
     *
     * Updates the tax record within a database transaction.
     *
     * @param  Tax  $tax  The tax model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Tax The freshly updated Tax model instance.
     */
    public function updateTax(Tax $tax, array $data): Tax
    {
        return DB::transaction(function () use ($tax, $data) {
            $tax->update($data);

            return $tax->fresh();
        });
    }

    /**
     * Delete a specific tax.
     *
     * Will abort if the tax is currently linked to any existing products.
     *
     * @param  Tax  $tax  The tax model instance to delete.
     *
     * @throws ConflictHttpException If the tax has associated products.
     */
    public function deleteTax(Tax $tax): void
    {
        if ($tax->products()->exists()) {
            throw new ConflictHttpException("Cannot delete tax '{$tax->name}' as it has associated products.");
        }

        DB::transaction(function () use ($tax) {
            $tax->delete();
        });
    }

    /**
     * Bulk delete multiple taxes.
     *
     * Iterates over an array of tax IDs and attempts to delete them.
     * Skips any taxes that have associated products to prevent database relationship errors.
     *
     * @param  array<int>  $ids  Array of tax IDs to be deleted.
     * @return int The total count of successfully deleted taxes.
     */
    public function bulkDeleteTaxes(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $taxes = Tax::query()->whereIn('id', $ids)->withCount('products')->get();
            $count = 0;

            foreach ($taxes as $tax) {
                if ($tax->products_count > 0) {
                    continue;
                }
                $tax->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple taxes.
     *
     * @param  array<int>  $ids  Array of tax IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Tax::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple taxes from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing tax data.
     */
    public function importTaxes(UploadedFile $file): void
    {
        ExcelFacade::import(new TaxesImport, $file);
    }

    /**
     * Retrieve the path to the sample taxes import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     */
    public function download(): string
    {
        $fileName = 'taxes-sample.csv';

        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template taxes not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing tax data.
     *
     * Compiles the requested tax data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific tax IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'taxes_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new TaxesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
