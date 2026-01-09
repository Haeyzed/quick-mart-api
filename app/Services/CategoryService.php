<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use App\Mail\ExportMail;
use App\Models\Category;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

/**
 * CategoryService
 *
 * Handles all business logic for category operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class CategoryService extends BaseService
{
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

            // Handle file upload if present
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $filePath = $this->uploadService->upload(
                    $data['image'],
                    'categories',
                    'public'
                );
                $data['image'] = $filePath;
                $data['image_url'] = $this->uploadService->url($filePath, 'public');
            }

            // Generate slug if not provided and name exists
            if (!isset($data['slug']) && isset($data['name'])) {
                $data['slug'] = Category::generateUniqueSlug($data['name']);
            }

            return Category::create($data);
        });
    }

    /**
     * Normalize category data to match database schema requirements.
     *
     * - is_active: stored as boolean (true/false)
     * - featured: stored as tinyint (0/1)
     * - is_sync_disable: stored as tinyint (0/1)
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeCategoryData(array $data): array
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

        // featured and is_sync_disable are stored as 0/1 (tinyint) in database
        if (!isset($data['featured'])) {
            $data['featured'] = 0;
        } else {
            $data['featured'] = filter_var(
                $data['featured'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
        }

        if (!isset($data['is_sync_disable'])) {
            $data['is_sync_disable'] = 0;
        } else {
            $data['is_sync_disable'] = filter_var(
                $data['is_sync_disable'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            ) ? 1 : 0;
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
        return $this->transaction(function () use ($category, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeCategoryData($data);

            // Handle file upload if present
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
     * @return string File path or download response
     */
    public function exportCategories(array $ids = [], string $format = 'excel', ?User $user = null): string
    {
        $fileName = 'categories-export-' . date('Y-m-d-His') . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $filePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new CategoriesExport($ids), $filePath, 'public');
        } else {
            // For PDF, use Excel's PDF export with DOMPDF
            Excel::store(new CategoriesExport($ids), $filePath, 'public', \Maatwebsite\Excel\Excel::DOMPDF);
        }

        // If user is provided, send email
        if ($user) {
            Mail::to($user->email)->send(new ExportMail(
                $user,
                $filePath,
                $fileName,
                'Categories'
            ));
        }

        return $filePath;
    }
}


