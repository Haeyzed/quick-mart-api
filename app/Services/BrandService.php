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
 *
 * Key Features:
 * - Encapsulates all brand-related database queries
 * - Handles file uploads and deletions
 * - Provides bulk operations for efficiency
 * - Manages data normalization and validation
 */
class BrandService extends BaseService
{
    use MailInfo;

    private const BULK_ACTIVATE = ['is_active' => true];
    private const BULK_DEACTIVATE = ['is_active' => false];

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
                    $search = $filters['search'];
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%");
                })
            )
            ->latest()
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
            $data = $this->normalizeBrandData($data);
            $data = $this->processFileUploads($data);

            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = Brand::generateUniqueSlug($data['name']);
            }

            return Brand::create($data);
        });
    }

    /**
     * Normalize brand data to match database schema requirements.
     *
     * Handles boolean conversions and default values:
     * - is_active: boolean, defaults to true on create
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation
     * @return array<string, mixed>
     */
    private function normalizeBrandData(array $data, bool $isUpdate = false): array
    {
        // is_active defaults to true on create
        if (!isset($data['is_active']) && !$isUpdate) {
            $data['is_active'] = true;
        } elseif (isset($data['is_active'])) {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Extracted file upload logic to reduce duplication across create/update
     * Process and upload image file.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function processFileUploads(array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $filePath = $this->uploadService->upload(
                $data['image'],
                'brands',
                'public'
            );
            $data['image'] = $filePath;
            $data['image_url'] = $this->uploadService->url($filePath, 'public');
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
            $data = $this->normalizeBrandData($data, isUpdate: true);

            // Delete old files before uploading new ones
            if (isset($data['image']) && $data['image'] instanceof UploadedFile && $brand->image) {
                $this->uploadService->delete($brand->image, 'public');
            }

            $data = $this->processFileUploads($data);

            $brand->update($data);
            return $brand->fresh();
        });
    }

    /**
     * Refactored from individual loop to batch processing with chunking
     * Bulk delete multiple brands using efficient batch operations.
     *
     * @param array<int> $ids Array of brand IDs to delete
     * @return int Number of brands successfully deleted
     */
    public function bulkDeleteBrands(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            $deletedCount = 0;

            Brand::whereIn('id', $ids)
                ->chunk(100, function ($brands) use (&$deletedCount) {
                    foreach ($brands as $brand) {
                        try {
                            if (!$brand->products()->exists()) {
                                $this->cleanupBrandFiles($brand);
                                $brand->delete();
                                $deletedCount++;
                            }
                        } catch (Exception $e) {
                            $this->logError("Failed to delete brand {$brand->id}: " . $e->getMessage());
                        }
                    }
                });

            return $deletedCount;
        });
    }

    /**
     * Delete a single brand with validation.
     *
     * Prevents deletion if brand has associated products.
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

            $this->cleanupBrandFiles($brand);
            return $brand->delete();
        });
    }

    /**
     * Extracted file cleanup logic to DRY principle
     * Clean up associated files for a brand.
     *
     * @param Brand $brand
     * @return void
     */
    private function cleanupBrandFiles(Brand $brand): void
    {
        if ($brand->image) {
            $this->uploadService->delete($brand->image, 'public');
        }
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
     * Created private helper method to eliminate duplication across all bulk update methods
     * Bulk update multiple brands with specified data.
     *
     * @param array<int> $ids Array of brand IDs to update
     * @param array<string, mixed> $updateData Data to update
     * @return int Number of brands updated
     */
    private function bulkUpdateBrands(array $ids, array $updateData): int
    {
        return $this->transaction(function () use ($ids, $updateData) {
            return Brand::whereIn('id', $ids)->update($updateData);
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
        return $this->bulkUpdateBrands($ids, self::BULK_ACTIVATE);
    }

    /**
     * Bulk deactivate multiple brands.
     *
     * @param array<int> $ids Array of brand IDs to deactivate
     * @return int Number of brands deactivated
     */
    public function bulkDeactivateBrands(array $ids): int
    {
        return $this->bulkUpdateBrands($ids, self::BULK_DEACTIVATE);
    }

    /**
     * Export brands to Excel or PDF.
     *
     * @param array<int> $ids Array of brand IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @param string $method Export method: 'download' or 'email'
     * @return string File path
     */
    public function exportBrands(
        array $ids = [],
        string $format = 'excel',
        ?User $user = null,
        array $columns = [],
        string $method = 'download'
    ): string {
        $fileName = 'brands-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        $this->generateExportFile($format, $filePath, $ids, $columns);

        if ($user && $method === 'email') {
            $this->sendExportEmail($user, $filePath, $fileName);
        }

        return $filePath;
    }

    /**
     * Extracted export file generation logic for cleaner separation of concerns
     * Generate export file in specified format.
     *
     * @param string $format
     * @param string $filePath
     * @param array<int> $ids
     * @param array<string> $columns
     * @return void
     */
    private function generateExportFile(string $format, string $filePath, array $ids, array $columns): void
    {
        if ($format === 'excel') {
            Excel::store(new BrandsExport($ids, $columns), $filePath, 'public');
        } else {
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
    }

    /**
     * Extracted email sending logic for better error handling and maintainability
     * Send export file via email.
     *
     * @param User $user
     * @param string $filePath
     * @param string $fileName
     * @return void
     * @throws HttpResponseException
     */
    private function sendExportEmail(User $user, string $filePath, string $fileName): void
    {
        $mailSetting = MailSetting::latest()->first();
        if (!$mailSetting) {
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'Mail settings are not configured. Please contact the administrator.'],
                    Response::HTTP_BAD_REQUEST
                )
            );
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
            $this->logError("Failed to send export email: " . $e->getMessage());
            throw new HttpResponseException(
                response()->json(
                    ['message' => 'Failed to send export email: ' . $e->getMessage()],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                )
            );
        }
    }
}

