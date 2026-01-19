<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use App\Mail\ExportMail;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf as PDF;

/**
 * CategoryService
 *
 * Handles all business logic for category operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 *
 * Key Features:
 * - Encapsulates all category-related database queries
 * - Handles file uploads and deletions
 * - Provides bulk operations for efficiency
 * - Manages data normalization and validation
 * - Enforces permission checks for all operations
 */
class CategoryService extends BaseService
{
    use CheckPermissionsTrait;
    use MailInfo;

    private const BULK_ACTIVATE = ['is_active' => true];
    private const BULK_DEACTIVATE = ['is_active' => false];
    private const BULK_ENABLE_FEATURED = ['featured' => 1];
    private const BULK_DISABLE_FEATURED = ['featured' => 0];
    private const BULK_ENABLE_SYNC = ['is_sync_disable' => 0];
    private const BULK_DISABLE_SYNC = ['is_sync_disable' => 1];

    public function __construct(
        private readonly UploadService $uploadService
    )
    {
    }

    /**
     * Get paginated list of categories with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, featured, is_sync_disable, parent_id, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Category>
     */
    public function getCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        // Check permission: user needs 'category' permission to view categories
        $this->requirePermission('category');

        return Category::query()
            ->with('parent:id,name')
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                isset($filters['featured']),
                fn($query) => $query->where('featured', (bool)$filters['featured'])
            )
            ->when(
                isset($filters['is_sync_disable']),
                fn($query) => $query->where('is_sync_disable', (bool)$filters['is_sync_disable'])
            )
            ->when(
                isset($filters['parent_id']),
                fn($query) => $query->where('parent_id', $filters['parent_id'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where(function ($q) use ($filters) {
                    $search = $filters['search'];
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                })
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single category by ID.
     *
     * @param int $id Category ID
     * @return Category
     */
    public function getCategory(int $id): Category
    {
        // Check permission: user needs 'category' permission to view categories
        $this->requirePermission('category');

        return Category::with('parent:id,name')->findOrFail($id);
    }

    /**
     * Create a new category.
     *
     * @param array<string, mixed> $data Validated category data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        // Check permission: user needs 'category' permission to create categories
        $this->requirePermission('category');

        return $this->transaction(function () use ($data) {
            $data = $this->normalizeCategoryData($data);
            $data = $this->processFileUploads($data);

            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = Category::generateSlug($data['name']);
            }

            return Category::create($data);
        });
    }

    /**
     * Normalize category data to match database schema requirements.
     *
     * Handles boolean conversions and default values:
     * - is_active: boolean, defaults to true on create
     * - featured: tinyint (0/1), defaults to 0 on create
     * - is_sync_disable: tinyint (0/1) or null, only set if provided
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation
     * @return array<string, mixed>
     */
    private function normalizeCategoryData(array $data, bool $isUpdate = false): array
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

        // featured: stored as 0/1 (tinyint)
        if (isset($data['featured'])) {
            $data['featured'] = filter_var(
                $data['featured'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
        } elseif (!$isUpdate) {
            $data['featured'] = 0;
        }

        // is_sync_disable: only set if provided
        if (isset($data['is_sync_disable'])) {
            $data['is_sync_disable'] = filter_var(
                $data['is_sync_disable'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
        } elseif (!$isUpdate) {
            unset($data['is_sync_disable']);
        }

        return $data;
    }

    /**
     * Extracted file upload logic to reduce duplication across create/update
     * Process and upload image and icon files.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function processFileUploads(array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $filePath = $this->uploadService->upload(
                $data['image'],
                config('storage.categories.images')
            );
            $data['image'] = $filePath;
            $data['image_url'] = $this->uploadService->url($filePath);
        }

        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $iconPath = $this->uploadService->upload(
                $data['icon'],
                config('storage.categories.icons')
            );
            $data['icon'] = $iconPath;
            $data['icon_url'] = $this->uploadService->url($iconPath);
        }

        return $data;
    }

    /**
     * Update an existing category.
     *
     * @param Category $category Category instance to update
     * @param array<string, mixed> $data Validated category data
     * @return Category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->transaction(function () use ($category, $data) {
            $data = $this->normalizeCategoryData($data, isUpdate: true);

            // Delete old files before uploading new ones
            if (isset($data['image']) && $data['image'] instanceof UploadedFile && $category->image) {
                $this->uploadService->delete($category->image);
            }

            if (isset($data['icon']) && $data['icon'] instanceof UploadedFile && $category->icon) {
                $this->uploadService->delete($category->icon);
            }

            $data = $this->processFileUploads($data);

            if (!isset($data['featured']) && Schema::hasColumn('categories', 'featured')) {
                $data['featured'] = 0;
            }

            if (!isset($data['is_sync_disable']) && Schema::hasColumn('categories', 'is_sync_disable')) {
                $data['is_sync_disable'] = null;
            }

            $category->update($data);
            return $category->fresh();
        });
    }

    /**
     * Delete a single category with validation.
     *
     * Prevents deletion if category has children or associated products.
     *
     * @param Category $category Category instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteCategory(Category $category): bool
    {
        // Check permission: user needs 'category' permission to delete categories
        $this->requirePermission('category');

        return $this->transaction(function () use ($category) {
            if ($category->children()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete category: category has child categories');
            }

            if ($category->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete category: category has associated products');
            }

            $this->cleanupCategoryFiles($category);
            return $category->delete();
        });
    }

    /**
     * Refactored from individual loop to batch processing with chunking
     * Bulk delete multiple categories using efficient batch operations.
     *
     * @param array<int> $ids Array of category IDs to delete
     * @return int Number of categories successfully deleted
     */
    public function bulkDeleteCategories(array $ids): int
    {
        // Check permission: user needs 'category' permission to delete categories
        $this->requirePermission('category');

        return $this->transaction(function () use ($ids) {
            $deletedCount = 0;

            Category::whereIn('id', $ids)
                ->chunk(100, function ($categories) use (&$deletedCount) {
                    foreach ($categories as $category) {
                        try {
                            if (!$category->children()->exists() && !$category->products()->exists()) {
                                $this->cleanupCategoryFiles($category);
                                $category->delete();
                                $deletedCount++;
                            }
                        } catch (Exception $e) {
                            $this->logError("Failed to delete category {$category->id}: " . $e->getMessage());
                        }
                    }
                });

            return $deletedCount;
        });
    }

    /**
     * Extracted file cleanup logic to DRY principle
     * Clean up associated files for a category.
     *
     * @param Category $category
     * @return void
     */
    private function cleanupCategoryFiles(Category $category): void
    {
        if ($category->image) {
            $this->uploadService->delete($category->image, 'public');
        }

        if ($category->icon) {
            $this->uploadService->delete($category->icon, 'public');
        }
    }

    /**
     * Get all root categories (categories without parent).
     *
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection
    {
        // Check permission: user needs 'category' permission to view categories
        $this->requirePermission('category');

        return Category::root()->active()->get();
    }

    /**
     * Get categories with their children (nested structure).
     *
     * @return Collection<int, Category>
     */
    public function getCategoriesWithChildren(): Collection
    {
        // Check permission: user needs 'category' permission to view categories
        $this->requirePermission('category');

        return Category::with('children')
            ->whereNull('parent_id')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Import categories from a file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function importCategories(UploadedFile $file): void
    {
        // Check permission: user needs 'category' permission to import categories
        $this->requirePermission('category');

        $this->transaction(function () use ($file) {
            Excel::import(new CategoriesImport(), $file);
        });
    }

    /**
     * Created private helper method to eliminate duplication across all bulk update methods
     * Bulk update multiple categories with specified data.
     *
     * @param array<int> $ids Array of category IDs to update
     * @param array<string, mixed> $updateData Data to update
     * @return int Number of categories updated
     */
    private function bulkUpdateCategories(array $ids, array $updateData): int
    {
        return $this->transaction(function () use ($ids, $updateData) {
            return Category::whereIn('id', $ids)->update($updateData);
        });
    }

    /**
     * Bulk activate multiple categories.
     *
     * @param array<int> $ids Array of category IDs to activate
     * @return int Number of categories activated
     */
    public function bulkActivateCategories(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_ACTIVATE);
    }

    /**
     * Bulk deactivate multiple categories.
     *
     * @param array<int> $ids Array of category IDs to deactivate
     * @return int Number of categories deactivated
     */
    public function bulkDeactivateCategories(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_DEACTIVATE);
    }

    /**
     * Bulk enable featured status for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to enable featured
     * @return int Number of categories updated
     */
    public function bulkEnableFeatured(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_ENABLE_FEATURED);
    }

    /**
     * Bulk disable featured status for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to disable featured
     * @return int Number of categories updated
     */
    public function bulkDisableFeatured(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_DISABLE_FEATURED);
    }

    /**
     * Bulk enable sync (set is_sync_disable to 0) for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to enable sync
     * @return int Number of categories updated
     */
    public function bulkEnableSync(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_ENABLE_SYNC);
    }

    /**
     * Bulk disable sync (set is_sync_disable to 1) for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to disable sync
     * @return int Number of categories updated
     */
    public function bulkDisableSync(array $ids): int
    {
        // Check permission: user needs 'category' permission to update categories
        $this->requirePermission('category');

        return $this->bulkUpdateCategories($ids, self::BULK_DISABLE_SYNC);
    }

    /**
     * Export categories to Excel or PDF.
     *
     * @param array<int> $ids Array of category IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @param string $method Export method: 'download' or 'email'
     * @return string File path
     */
    public function exportCategories(
        array $ids = [],
        string $format = 'excel',
        ?User $user = null,
        array $columns = [],
        string $method = 'download'
    ): string {
        // Check permission: user needs 'category' permission to export categories
        $this->requirePermission('category');

        $fileName = 'categories-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
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
            Excel::store(new CategoriesExport($ids, $columns), $filePath, 'public');
        } else {
            $categories = Category::with('parent:id,name')
                ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.categories-pdf', [
                'categories' => $categories,
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
                'Categories',
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
