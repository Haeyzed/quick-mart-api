<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Mail\ExportMail;
use App\Models\Brand;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Class BrandService
 *
 * Centralizes business logic for Brand management, including
 * database interactions, file handling, imports, and exports.
 */
class BrandService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * BrandService constructor.
     *
     * @param UploadService $uploadService
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Retrieve brands with filters and pagination.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBrands(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('brands-index');

        return Brand::query()
            ->when(isset($filters['status']), fn ($q) => 
                $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($subQ) => $subQ
                    ->where('name', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                );
            })
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
        $this->requirePermission('brands-create');

        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
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
        $this->requirePermission('brands-update');

        return DB::transaction(function () use ($brand, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($brand->image) {
                    $this->uploadService->delete($brand->image);
                }
                $data = $this->handleImageUpload($data);
            }

            $brand->update($data);
            return $brand->fresh();
        });
    }

    /**
     * Delete a brand.
     *
     * @param Brand $brand
     * @throws UnprocessableEntityHttpException
     */
    public function deleteBrand(Brand $brand): void
    {
        $this->requirePermission('brands-delete');

        if ($brand->products()->exists()) {
            throw new UnprocessableEntityHttpException("Cannot delete brand '{$brand->name}' because it has associated products.");
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
        $this->requirePermission('brands-delete');

        return DB::transaction(function () use ($ids) {
            // Eager load product count to avoid N+1 and prevent invalid deletes
            $brands = Brand::whereIn('id', $ids)->withCount('products')->get();
            $deletedCount = 0;

            foreach ($brands as $brand) {
                // Skip brands with products
                if ($brand->products_count > 0) {
                    continue;
                }

                if ($brand->image) {
                    $this->uploadService->delete($brand->image);
                }
                
                $brand->delete();
                $deletedCount++;
            }

            return $deletedCount;
        });
    }

    /**
     * Bulk activate brands.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkActivateBrands(array $ids): int
    {
        $this->requirePermission('brands-update');
        return Brand::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * Bulk deactivate brands.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkDeactivateBrands(array $ids): int
    {
        $this->requirePermission('brands-update');
        return Brand::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Import brands from Excel/CSV.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importBrands(UploadedFile $file): void
    {
        $this->requirePermission('brands-import');
        Excel::import(new BrandsImport(), $file);
    }

    /**
     * Export brands to file.
     *
     * @param array<int> $ids
     * @param string $format
     * @param User|null $user
     * @param array<string> $columns
     * @param string $method
     * @return string
     */
    public function exportBrands(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('brands-export');

        $fileName = 'brands_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new BrandsExport($ids, $columns), $relativePath, 'public');
        } else {
            // Retrieve data for PDF
            $brands = Brand::query()
                ->when(!empty($ids), fn ($q) => $q->whereIn('id', $ids))
                ->get();
                
            $pdf = PDF::loadView('exports.brands-pdf', compact('brands', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName);
        }

        return $relativePath;
    }

    /**
     * Handle image upload logic.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function handleImageUpload(array $data): array
    {
        $path = $this->uploadService->upload(
            $data['image'], 
            config('storage.brands.images', 'brands')
        );

        $data['image'] = $path;
        $data['image_url'] = $this->uploadService->url($path);

        return $data;
    }

    /**
     * Send export email.
     *
     * @param User $user
     * @param string $path
     * @param string $fileName
     * @throws Exception
     */
    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::latest()->firstOr(fn() => throw new Exception("Mail settings not configured."));
        $generalSetting = GeneralSetting::latest()->first();

        $this->setMailInfo($mailSetting);
        
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Brands List', $generalSetting)
        );
    }
}