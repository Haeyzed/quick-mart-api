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
use App\Traits\MailInfo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * BrandService
 *
 * Handles all business logic for brand operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class BrandService extends BaseService
{
    use MailInfo;
    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated list of brands with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Brand>
     */
    public function getBrands(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Brand::query()
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('slug', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('short_description', 'like', '%' . $filters['search'] . '%');
                })
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single brand by ID.
     *
     * @param int $id Brand ID
     * @return Brand
     */
    public function getBrand(int $id): Brand
    {
        return Brand::findOrFail($id);
    }

    /**
     * Create a new brand.
     *
     * @param array<string, mixed> $data Validated brand data
     * @return Brand
     */
    public function createBrand(array $data): Brand
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeBrandData($data);

            // Handle file upload if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $filePath = $this->uploadService->upload(
                    $data['image'],
                    'brands',
                    'public'
                );
                $data['image'] = $filePath;
                $data['image_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Generate slug if not provided and name exists
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = Brand::generateUniqueSlug($data['name']);
            }

            return Brand::create($data);
        });
    }

    /**
     * Normalize brand data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeBrandData(array $data): array
    {
        // is_active is stored as boolean (true/false)
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing brand.
     *
     * @param Brand $brand Brand instance to update
     * @param array<string, mixed> $data Validated brand data
     * @return Brand
     */
    public function updateBrand(Brand $brand, array $data): Brand
    {
        return $this->transaction(function () use ($brand, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeBrandData($data);

            // Handle file upload if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                // Delete old image if exists
                if ($brand->image) {
                    $this->uploadService->delete($brand->image, 'public');
                }

                $filePath = $this->uploadService->upload(
                    $data['image'],
                    'brands',
                    'public'
                );
                $data['image'] = $filePath;
                $data['image_url'] = $this->uploadService->url($filePath, 'public');
            }

            $brand->update($data);
            return $brand->fresh();
        });
    }

    /**
     * Bulk delete multiple brands.
     *
     * @param array<int> $ids Array of brand IDs to delete
     * @return int Number of brands deleted
     */
    public function bulkDeleteBrands(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $brand = Brand::findOrFail($id);
                $this->deleteBrand($brand);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete brand {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single brand.
     *
     * @param Brand $brand Brand instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteBrand(Brand $brand): bool
    {
        return $this->transaction(function () use ($brand) {
            if ($brand->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete brand: brand has associated products');
            }

            // Delete the image file if it exists
            if ($brand->image) {
                $this->uploadService->delete($brand->image, 'public');
            }

            return $brand->delete();
        });
    }

    /**
     * Import brands from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importBrands(UploadedFile $file): void
    {
        $this->transaction(function () use ($file) {
            Excel::import(new BrandsImport(), $file);
        });
    }

    /**
     * Bulk activate multiple brands.
     *
     * @param array<int> $ids Array of brand IDs to activate
     * @return int Number of brands activated
     */
    public function bulkActivateBrands(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Brand::whereIn('id', $ids)
                ->update(['is_active' => true]);
        });
    }

    /**
     * Bulk deactivate multiple brands.
     *
     * @param array<int> $ids Array of brand IDs to deactivate
     * @return int Number of brands deactivated
     */
    public function bulkDeactivateBrands(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Brand::whereIn('id', $ids)
                ->update(['is_active' => false]);
        });
    }

    /**
     * Export brands to Excel or PDF.
     *
     * @param array<int> $ids Array of brand IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @return string File path or download response
     */
    public function exportBrands(array $ids = [], string $format = 'excel', ?User $user = null, array $columns = [], string $method = 'download'): string
    {
        $fileName = 'brands-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new BrandsExport($ids, $columns), $filePath, 'public');
        } else {
            // For PDF, export data first then create PDF view
            $brands = Brand::query()
                ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.brands-pdf', [
                'brands' => $brands,
                'columns' => $columns,
            ]);
            Storage::disk('public')->put($filePath, $pdf->output());
        }

        // If user is provided, send email
        if ($user && $method === 'email') {
            $mailSetting = MailSetting::latest()->first();
            if (!$mailSetting) {
                abort(Response::HTTP_BAD_REQUEST, 'Mail settings are not configured. Please contact the administrator.');
            }

            $generalSetting = GeneralSetting::latest()->first();

            try {
                $this->setMailInfo($mailSetting);
                Mail::to($user->email)->send(new ExportMail(
                    $user,
                    $filePath,
                    $fileName,
                    'Brands',
                    $generalSetting
                ));
            } catch (Exception $e) {
                // Log error but don't fail the export
                $this->logError("Failed to send export email: " . $e->getMessage());
                abort(Response::HTTP_INTERNAL_SERVER_ERROR, 'Failed to send export email: ' . $e->getMessage());
            }
        }

        return $filePath;
    }
}

