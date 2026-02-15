<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\WarehousesExport;
use App\Imports\WarehousesImport;
use App\Models\Product;
use App\Models\ProductWarehouse;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class WarehouseService
 * Handles business logic for Warehouses.
 */
class WarehouseService
{
    /**
     *
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * @param UploadService $uploadService
     */
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated warehouses based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedWarehouses(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Warehouse::query()
            ->filter($filters)
            ->withCount(['productWarehouses as number_of_products' => fn($q) => $q->where('qty', '>', 0)])
            ->withSum(['productWarehouses as stock_quantity' => fn($q) => $q->where('qty', '>', 0)], 'qty')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of warehouse options.
     * Returns value/label format for select/combobox components.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return Warehouse::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(Warehouse $warehouse) => [
                'value' => $warehouse->id,
                'label' => $warehouse->name,
            ]);
    }

    /**
     * Create a new warehouse.
     *
     * @param array<string, mixed> $data
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
     * Update an existing warehouse.
     *
     * @param array<string, mixed> $data
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
     * Bulk delete warehouses.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
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
     * Update status for multiple warehouses.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Warehouse::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import warehouses from file.
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
        $fileName = "brands-sample.csv";

        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException("Template brands not found.");
        }

        return $path;
    }

    /**
     * Export warehouses to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'warehouses_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
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
