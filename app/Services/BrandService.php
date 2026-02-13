<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class BrandService
 * Handles business logic for Brands.
 */
class BrandService
{
    private const BRAND_IMAGE_PATH = 'images/brands';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated brands based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedBrands(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                    );
                }
            )
            ->when(
                ! empty($filters['start_date']),
                fn (Builder $q) => $q->whereDate('created_at', '>=', $filters['start_date'])
            )
            ->when(
                ! empty($filters['end_date']),
                fn (Builder $q) => $q->whereDate('created_at', '<=', $filters['end_date'])
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new brand.
     *
     * @param  array<string, mixed>  $data
     */
    public function createBrand(array $data): Brand
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $uploadResult = $this->handleImageUpload($data['image']);
                $data['image'] = $uploadResult['path'];
                $data['image_url'] = $uploadResult['url'];
            }

            return Brand::create($data);
        });
    }

    /**
     * Update an existing brand.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        return DB::transaction(function () use ($brand, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                // Delete old image
                if ($brand->image) {
                    $this->uploadService->delete($brand->image);
                }

                $uploadResult = $this->handleImageUpload($data['image']);
                $data['image'] = $uploadResult['path'];
                $data['image_url'] = $uploadResult['url'];
            }

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
            if ($brand->image) {
                $this->uploadService->delete($brand->image);
            }
            $brand->delete();
        });
    }

    /**
     * Bulk delete brands.
     *
     * @param  array<int>  $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteBrands(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $brands = Brand::whereIn('id', $ids)->withCount('products')->get();
            $count = 0;

            foreach ($brands as $brand) {
                if ($brand->products_count > 0) {
                    continue;
                }

                if ($brand->image) {
                    $this->uploadService->delete($brand->image);
                }

                $brand->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update status for multiple brands.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Brand::whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import brands from file.
     */
    public function importBrands(UploadedFile $file): void
    {
        ExcelFacade::import(new BrandsImport, $file);
    }

    /**
     * Export brands to file.
     *
     * @param  array<int>  $ids
     * @param  string  $format  'excel' or 'pdf'
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters for created_at
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'brands_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new BrandsExport($ids, $columns, $filters['start_date'] ?? null, $filters['end_date'] ?? null),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }

    /**
     * Handle Image Upload via UploadService.
     *
     * @return array{path: string, url: string|null}
     */
    private function handleImageUpload(UploadedFile $file): array
    {
        $path = $this->uploadService->upload($file, self::BRAND_IMAGE_PATH);
        $url = $this->uploadService->url($path);

        return [
            'path' => $path,
            'url' => $url,
        ];
    }
}
