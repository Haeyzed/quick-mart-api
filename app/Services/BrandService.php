<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Models\Brand;
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
 * Class BrandService
 * * Handles all core business logic and database interactions for Brands.
 * Acts as the intermediary between the controllers and the database layer.
 */
class BrandService
{
    /**
     * The storage path for brand image uploads.
     */
    private const BRANDS_IMAGE_PATH = 'images/brands';

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * BrandService constructor.
     *
     * @param UploadService $uploadService Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated brands based on provided filters.
     *
     * Retrieves a paginated list of brands, applying scopes for searching,
     * status filtering, and date ranges.
     *
     * @param array<string, mixed> $filters An associative array of filters (e.g., 'search', 'is_active', 'start_date', 'end_date').
     * @param int $perPage The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Brand models.
     */
    public function getPaginatedBrands(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a lightweight list of active brand options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active brands.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Brand::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(Brand $brand) => [
                'value' => $brand->id,
                'label' => $brand->name,
            ]);
    }

    /**
     * Create a newly registered brand.
     *
     * Processes file uploads if an image is provided and stores the new brand record
     * within a database transaction to ensure data integrity.
     *
     * @param array<string, mixed> $data The validated request data for the new brand.
     * @return Brand The newly created Brand model instance.
     */
    public function createBrand(array $data): Brand
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);
            return Brand::query()->create($data);
        });
    }

    /**
     * Handle Image/Icon Upload via UploadService.
     *
     * Checks if an image file is present in the data array. If so, it deletes the
     * old image (if updating) and uploads the new one, injecting the paths into the data array.
     *
     * @param array<string, mixed> $data The input data potentially containing an 'image' file.
     * @param Brand|null $brand The existing brand model if performing an update.
     * @return array<string, mixed> The modified data array with uploaded file paths.
     */
    private function handleUploads(array $data, ?Brand $brand = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($brand?->image) {
                $this->uploadService->delete($brand->image);
            }
            $path = $this->uploadService->upload($data['image'], self::BRANDS_IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Update an existing brand's information.
     *
     * Processes potential new image uploads and updates the brand record within
     * a database transaction.
     *
     * @param Brand $brand The brand model instance to update.
     * @param array<string, mixed> $data The validated update data.
     * @return Brand The freshly updated Brand model instance.
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        return DB::transaction(function () use ($brand, $data) {
            $data = $this->handleUploads($data, $brand);
            $brand->update($data);
            return $brand->fresh();
        });
    }

    /**
     * Delete a specific brand.
     *
     * Deletes the brand and its associated image file. Will abort if the brand
     * is currently linked to any existing products.
     *
     * @param Brand $brand The brand model instance to delete.
     * @throws ConflictHttpException If the brand has associated products.
     * @return void
     */
    public function deleteBrand(Brand $brand): void
    {
        if ($brand->products()->exists()) {
            throw new ConflictHttpException("Cannot delete brand '{$brand->name}' as it has associated products.");
        }

        DB::transaction(function () use ($brand) {
            $this->cleanupFiles($brand);
            $brand->delete();
        });
    }

    /**
     * Remove files associated with a brand.
     *
     * @param Brand $brand The brand model whose files should be removed from storage.
     * @return void
     */
    private function cleanupFiles(Brand $brand): void
    {
        if ($brand->image) {
            $this->uploadService->delete($brand->image);
        }
    }

    /**
     * Bulk delete multiple brands.
     *
     * Iterates over an array of brand IDs and attempts to delete them.
     * Skips any brands that have associated products to prevent database relationship errors.
     *
     * @param array<int> $ids Array of brand IDs to be deleted.
     * @return int The total count of successfully deleted brands.
     */
    public function bulkDeleteBrands(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $brands = Brand::query()->whereIn('id', $ids)->withCount('products')->get();
            $count = 0;

            foreach ($brands as $brand) {
                if ($brand->products_count > 0) {
                    continue;
                }

                $this->cleanupFiles($brand);
                $brand->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple brands.
     *
     * @param array<int> $ids Array of brand IDs to update.
     * @param bool $isActive The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Brand::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import multiple brands from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param UploadedFile $file The uploaded spreadsheet file containing brand data.
     * @return void
     */
    public function importBrands(UploadedFile $file): void
    {
        ExcelFacade::import(new BrandsImport, $file);
    }

    /**
     * Retrieve the path to the sample brands import template.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     * @return string The absolute file path to the sample CSV.
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
     * Generate an export file (Excel or PDF) containing brand data.
     *
     * Compiles the requested brand data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param array<int> $ids Specific brand IDs to export (leave empty to export all based on filters).
     * @param string $format The file format requested ('excel' or 'pdf').
     * @param array<string> $columns Specific column names to include in the export.
     * @param array{start_date?: string, end_date?: string} $filters Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'brands_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new BrandsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
