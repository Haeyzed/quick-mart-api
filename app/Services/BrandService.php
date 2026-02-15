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
 * Handles business logic for Brands.
 */
class BrandService
{
    /**
     *
     */
    private const IMAGE_PATH = 'images/brands';
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
     * Get paginated brands based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedBrands(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of brand options.
     * Returns value/label format for select/combobox components.
     *
     * @return Collection<int, array{value: int, label: string}>
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
     * Create a new brand.
     *
     * @param array<string, mixed> $data
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
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function handleUploads(array $data, ?Brand $brand = null): array
    {
        // Handle Image
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($brand?->image) {
                $this->uploadService->delete($brand->image);
            }
            $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Update an existing brand.
     *
     * @param array<string, mixed> $data
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
     * Delete a brand.
     *
     * @throws ConflictHttpException
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
     * Remove associated files.
     */
    private function cleanupFiles(Brand $brand): void
    {
        if ($brand->image) {
            $this->uploadService->delete($brand->image);
        }
    }

    /**
     * Bulk delete brands.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
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
     * Update status for multiple brands.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Brand::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import brands from file.
     */
    public function importBrands(UploadedFile $file): void
    {
        ExcelFacade::import(new BrandsImport, $file);
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
     * Export brands to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters Optional date filters for created_at
     * @return string Relative file path.
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
