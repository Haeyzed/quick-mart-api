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
 */
class CategoryService extends BaseService
{
    use MailInfo;
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
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeCategoryData($data);

            // Handle image file upload if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $filePath = $this->uploadService->upload(
                    $data['image'],
                    'categories',
                    'public'
                );
                $data['image'] = $filePath;
                $data['image_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Handle icon file upload if present
            if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
                $iconPath = $this->uploadService->upload(
                    $data['icon'],
                    'categories/icons',
                    'public'
                );
                $data['icon'] = $iconPath;
                $data['icon_url'] = $this->uploadService->url($iconPath, 'public');
            }

            // Generate slug if not provided and name exists
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = Category::generateSlug($data['name']);
            }

            return Category::create($data);
        });
    }

    /**
     * Normalize category data to match database schema requirements.
     *
     * - is_active: stored as boolean (true/false), defaults to true on create
     * - featured: stored as tinyint (0/1)
     * - is_sync_disable: stored as tinyint (0/1) or null
     *
     * @param array<string, mixed> $data
     * @param bool $isUpdate Whether this is an update operation (affects default values)
     * @return array<string, mixed>
     */
    private function normalizeCategoryData(array $data, bool $isUpdate = false): array
    {
        // is_active defaults to true on create (matches old implementation: $lims_category_data['is_active'] = true;)
        if (!isset($data['is_active']) && !$isUpdate) {
            $data['is_active'] = true;
        } elseif (isset($data['is_active'])) {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        // featured: stored as 0/1 (tinyint) in database
        if (isset($data['featured'])) {
            $data['featured'] = filter_var(
                $data['featured'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
        } elseif (!$isUpdate) {
            // On create, default to 0 if not provided
            $data['featured'] = 0;
        }
        // On update, if not provided, leave it unset (will be handled in updateCategory method)

        // is_sync_disable: only set if provided (matches old implementation)
        if (isset($data['is_sync_disable'])) {
            $data['is_sync_disable'] = filter_var(
                $data['is_sync_disable'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
        } elseif (!$isUpdate) {
            // On create, don't set if not provided (leave as null in database)
            unset($data['is_sync_disable']);
        }
        // On update, if not provided, leave it unset (will be handled in updateCategory method)

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
        return $this->transaction(function () use ($category, $data) {
            // Normalize data to match database schema (pass isUpdate=true for update operations)
            $data = $this->normalizeCategoryData($data, isUpdate: true);

            // Handle image file upload if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                // Delete old image if exists
                if ($category->image) {
                    $this->uploadService->delete($category->image, 'public');
                }

                $filePath = $this->uploadService->upload(
                    $data['image'],
                    'categories',
                    'public'
                );
                $data['image'] = $filePath;
                $data['image_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Handle icon file upload if present
            if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
                // Delete old icon if exists
                if ($category->icon) {
                    $this->uploadService->delete($category->icon, 'public');
                }

                $iconPath = $this->uploadService->upload(
                    $data['icon'],
                    'categories/icons',
                    'public'
                );
                $data['icon'] = $iconPath;
                $data['icon_url'] = $this->uploadService->url($iconPath, 'public');
            }

            // Handle update-specific logic (matches old controller behavior)
            // If featured is not provided, set it to 0 (matches old: if(!isset($request->featured) && \Schema::hasColumn('categories', 'featured') ){ $input['featured'] = 0; })
            if (!isset($data['featured']) && Schema::hasColumn('categories', 'featured')) {
                $data['featured'] = 0;
            }

            // If is_sync_disable is not provided, set it to null (matches old: if(!isset($input['is_sync_disable']) && \Schema::hasColumn('categories', 'is_sync_disable')) $input['is_sync_disable'] = null;)
            if (!isset($data['is_sync_disable']) && Schema::hasColumn('categories', 'is_sync_disable')) {
                $data['is_sync_disable'] = null;
            }

            $category->update($data);
            return $category->fresh();
        });
    }

    /**
     * Bulk delete multiple categories.
     *
     * @param array<int> $ids Array of category IDs to delete
     * @return int Number of categories deleted
     */
    public function bulkDeleteCategories(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $category = Category::findOrFail($id);
                $this->deleteCategory($category);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete category {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single category.
     *
     * @param Category $category Category instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteCategory(Category $category): bool
    {
        return $this->transaction(function () use ($category) {
            if ($category->children()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete category: category has child categories');
            }

            if ($category->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete category: category has associated products');
            }

            // Delete the image file if it exists
            if ($category->image) {
                $this->uploadService->delete($category->image, 'public');
            }

            // Delete icon file if exists
            if ($category->icon) {
                $this->uploadService->delete($category->icon, 'public');
            }

            return $category->delete();
        });
    }

    /**
     * Get all root categories (categories without parent).
     *
     * @return Collection<int, Category>
     */
    public function getRootCategories(): Collection
    {
        return Category::root()->active()->get();
    }

    /**
     * Get categories with their children (nested structure).
     *
     * @return Collection<int, Category>
     */
    public function getCategoriesWithChildren(): Collection
    {
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
        $this->transaction(function () use ($file) {
            Excel::import(new CategoriesImport(), $file);
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
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['is_active' => true]);
        });
    }

    /**
     * Bulk deactivate multiple categories.
     *
     * @param array<int> $ids Array of category IDs to deactivate
     * @return int Number of categories deactivated
     */
    public function bulkDeactivateCategories(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['is_active' => false]);
        });
    }

    /**
     * Bulk enable featured status for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to enable featured
     * @return int Number of categories updated
     */
    public function bulkEnableFeatured(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['featured' => 1]);
        });
    }

    /**
     * Bulk disable featured status for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to disable featured
     * @return int Number of categories updated
     */
    public function bulkDisableFeatured(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['featured' => 0]);
        });
    }

    /**
     * Bulk enable sync (set is_sync_disable to 0) for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to enable sync
     * @return int Number of categories updated
     */
    public function bulkEnableSync(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['is_sync_disable' => 0]);
        });
    }

    /**
     * Bulk disable sync (set is_sync_disable to 1) for multiple categories.
     *
     * @param array<int> $ids Array of category IDs to disable sync
     * @return int Number of categories updated
     */
    public function bulkDisableSync(array $ids): int
    {
        return $this->transaction(function () use ($ids) {
            return Category::whereIn('id', $ids)
                ->update(['is_sync_disable' => 1]);
        });
    }

    /**
     * Export categories to Excel or PDF.
     *
     * @param array<int> $ids Array of category IDs to export (empty for all)
     * @param string $format Export format: 'excel' or 'pdf'
     * @param User|null $user User to send email to (null for download)
     * @param array<string> $columns Columns to export
     * @return string File path or download response
     */
    public function exportCategories(array $ids = [], string $format = 'excel', ?User $user = null, array $columns = [], string $method = 'download'): string
    {
        $fileName = 'categories-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            // Always store file - needed for both download (temporary) and email (attachment)
            Excel::store(new CategoriesExport($ids, $columns), $filePath, 'public');
        } else {
            // For PDF, export data first then create PDF view
            $categories = Category::with('parent:id,name')
                ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.categories-pdf', [
                'categories' => $categories,
                'columns' => $columns,
            ]);
            
            // Always store file - needed for both download (temporary) and email (attachment)
            Storage::disk('public')->put($filePath, $pdf->output());
        }

        // If user is provided, send email
        if ($user && $method === 'email') {
            // Check mail settings before attempting to send email
            $mailSetting = MailSetting::latest()->first();
            if (!$mailSetting) {
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Mail settings are not configured. Please contact the administrator.',
                    ], Response::HTTP_BAD_REQUEST)
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
                // Log error and return error response instead of aborting
                $this->logError("Failed to send export email: " . $e->getMessage());
                throw new HttpResponseException(
                    response()->json([
                        'message' => 'Failed to send export email: ' . $e->getMessage(),
                    ], Response::HTTP_INTERNAL_SERVER_ERROR)
                );
            }
        }

        return $filePath;
    }
}


