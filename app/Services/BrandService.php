<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\User;
use App\Models\MailSetting;
use App\Models\GeneralSetting;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use App\Exports\BrandsExport;
use App\Imports\BrandsImport;
use App\Mail\ExportMail;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * Class BrandService
 * * Centralizes Brand business logic, file storage orchestration, and export/import workflows.
 */
class BrandService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * @param UploadService $uploadService
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Retrieve brands with criteria-based filtering and pagination.
     * * @param array $filters Query parameters (status, search).
     * @param int $perPage Results per page.
     * @return LengthAwarePaginator
     */
    public function getBrands(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('brands-index');

        return Brand::query()
            ->when(isset($filters['status']), function ($query) use ($filters) {
                return $query->where('is_active', $filters['status'] === 'active');
            })
            ->when(!empty($filters['search']), function ($query) use ($filters) {
                $term = "%{$filters['search']}%";
                return $query->where(fn($q) => 
                    $q->where('name', 'like', $term)
                      ->orWhere('slug', 'like', $term)
                      ->orWhere('short_description', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Orchestrate brand creation including file upload.
     * * @param array $data Validated data from request.
     * @return Brand
     */
    public function createBrand(array $data): Brand
    {
        $this->requirePermission('brands-create');

        return $this->transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }

            return Brand::create($data);
        });
    }

    /**
     * Update brand and perform storage cleanup of old images.
     * * @param Brand $brand Existing brand instance.
     * @param array $data Updated fields.
     * @return Brand
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        $this->requirePermission('brands-update');

        return $this->transaction(function () use ($brand, $data) {
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
     * Delete a brand and its associated assets after product check.
     * * @param Brand $brand
     * @throws Exception If brand has active products.
     */
    public function deleteBrand(Brand $brand): void
    {
        $this->requirePermission('brands-delete');

        if ($brand->products()->exists()) {
            throw new Exception("Cannot delete brand: associated products exist.");
        }

        $this->transaction(function () use ($brand) {
            if ($brand->image) {
                $this->uploadService->delete($brand->image);
            }
            $brand->delete();
        });
    }

    /**
     * Perform bulk deletion with individual file cleanup.
     * * @param array<int> $ids
     * @return int Count of deleted brands.
     */
    public function bulkDeleteBrands(array $ids): int
    {
        $this->requirePermission('brands-delete');

        return $this->transaction(function () use ($ids) {
            $brands = Brand::whereIn('id', $ids)->withCount('products')->get();
            $deletedCount = 0;

            foreach ($brands as $brand) {
                if ($brand->products_count === 0) {
                    if ($brand->image) {
                        $this->uploadService->delete($brand->image);
                    }
                    $brand->delete();
                    $deletedCount++;
                }
            }
            return $deletedCount;
        });
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkActivateBrands(array $ids): int
    {
        $this->requirePermission('brands-update');
        return Brand::whereIn('id', $ids)->update(['is_active' => true]);
    }

    /**
     * @param array $ids
     * @return int
     */
    public function bulkDeactivateBrands(array $ids): int
    {
        $this->requirePermission('brands-update');
        return Brand::whereIn('id', $ids)->update(['is_active' => false]);
    }

    /**
     * Map image upload into the data array using UploadService.
     * * @param array $data
     * @return array
     */
    private function handleImageUpload(array $data): array
    {
        $file = $data['image'];
        $path = $this->uploadService->upload($file, config('storage.brands.images', 'brands'));
        
        $data['image'] = $path;
        $data['image_url'] = $this->uploadService->url($path);

        return $data;
    }

    /**
     * Generate export file and handle delivery method.
     * * @return string File path on public disk.
     */
    public function exportBrands(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('brands-export');
        
        $fileName = 'brands_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $path = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new BrandsExport($ids, $columns), $path, 'public');
        } else {
            $brands = Brand::when(!empty($ids), fn($q) => $q->whereIn('id', $ids))->get();
            $pdf = PDF::loadView('exports.brands-pdf', compact('brands', 'columns'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $path, $fileName);
        }

        return $path;
    }

    /**
     * Private helper for export email delivery.
     */
    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::latest()->first() ?? throw new Exception("Mail settings not found.");
        $generalSetting = GeneralSetting::latest()->first();

        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(new ExportMail($user, $path, $fileName, 'Brands', $generalSetting));
    }

    /**
     * @param UploadedFile $file
     */
    public function importBrands(UploadedFile $file): void
    {
        $this->requirePermission('brands-import');
        Excel::import(new BrandsImport(), $file);
    }
}