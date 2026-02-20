<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class WarehouseService
 * * Handles all core business logic and database interactions for Warehouses.
 * Acts as the intermediary between the controllers and the database layer.
 */
class WarehouseService
{
    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * WarehouseService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated warehouses based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedWarehouses(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Warehouse::query()
            ->filter($filters)
            ->withCount(['productWarehouses as number_of_products' => fn ($q) => $q->where('qty', '>', 0)])
            ->withSum(['productWarehouses as stock_quantity' => fn ($q) => $q->where('qty', '>', 0)], 'qty')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active warehouse options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active warehouses.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Warehouse::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Warehouse $warehouse) => [
                'value' => $warehouse->id,
                'label' => $warehouse->name,
            ]);
    }

    /**
     * Create a newly registered warehouse.
     *
     * Creates the warehouse and initializes product-warehouse pivot records with zero quantity
     * for all existing products, within a database transaction.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new warehouse.
     * @return Warehouse The newly created Warehouse model instance.
     */
    public function createWarehouse(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            $warehouse = Warehouse::query()->create($data);

            $productIds = Product::query()->pluck('id');
            foreach ($productIds as $productId) {
                ProductWarehouse::query()->create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouse->id,
                    'qty' => 0,
                ]);
            }

            return $warehouse;
        });
    }

    /**
     * Update an existing warehouse's information.
     *
     * Updates the warehouse record within a database transaction.
     *
     * @param  Warehouse  $warehouse  The warehouse model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Warehouse The freshly updated Warehouse model instance.
     */
    public function updateWarehouse(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $warehouse->update($data);

            return $warehouse->fresh();
        });
    }

    /**
     * Delete a warehouse.
     */
    public function deleteWarehouse(Warehouse $warehouse): void
    {
        DB::transaction(function () use ($warehouse) {
            $warehouse->delete();
        });
    }

    /**
     * Bulk delete multiple warehouses.
     *
     * Iterates over an array of warehouse IDs and soft-deletes them within a transaction.
     *
     * @param  array<int>  $ids  Array of warehouse IDs to be deleted.
     * @return int The total count of successfully deleted warehouses.
     */
    public function bulkDeleteWarehouses(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $warehouses = Warehouse::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($warehouses as $warehouse) {
                $warehouse->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple warehouses.
     *
     * @param  array<int>  $ids  Array of warehouse IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Warehouse::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple warehouses from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing warehouse data.
     */
    public function importWarehouses(UploadedFile $file): void
    {
        ExcelFacade::import(new WarehousesImport, $file);
    }

    /**
     * Download a brands CSV template.
     */
    public function download(): string
    {
        $fileName = 'brands-sample.csv';

        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template brands not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing warehouse data.
     *
     * Compiles the requested warehouse data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific warehouse IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'warehouses_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new WarehousesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
