<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Models\Brand;
use App\Services\UploadService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
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
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedBrands(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                    );
                }
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new brand.
     *
     * @param array<string, mixed> $data
     * @return Brand
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
     * @param Brand $brand
     * @param array<string, mixed> $data
     * @return Brand
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
     * @param Brand $brand
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
     * @param array<int> $ids
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
     * @param array<int> $ids
     * @param bool $isActive
     * @return int
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Brand::whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Import brands from file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importBrands(UploadedFile $file): void
    {
        Excel::import(new BrandsImport(), $file);
    }

    /**
     * Export brands to file.
     *
     * @param array<int> $ids
     * @param string $format 'excel' or 'pdf'
     * @param array<string> $columns
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns): string
    {
        $fileName = 'brands_' . now()->timestamp;
        $relativePath = "exports/{$fileName}." . ($format === 'pdf' ? 'pdf' : 'xlsx');

        if ($format === 'excel') {
            Excel::store(new BrandsExport($ids, $columns), $relativePath, 'public');
        } else {
            $brands = Brand::query()
                ->when(!empty($ids), fn (Builder $q) => $q->whereIn('id', $ids))
                ->get();

            // Note: Ensure the view 'exports.brands-pdf' exists
            $pdf = Pdf::loadView('exports.brands-pdf', compact('brands', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        return $relativePath;
    }

    /**
     * Handle Image Upload via UploadService.
     *
     * @param UploadedFile $file
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
