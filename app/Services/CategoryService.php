<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use App\Models\Category;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * Class CategoryService
 * * Handles all core business logic and database interactions for Categories.
 * Acts as the intermediary between the controllers and the database layer.
 */
class CategoryService
{
    /**
     * The storage path for category image uploads.
     */
    private const IMAGE_PATH = 'images/categories';

    /**
     * The storage path for category icon uploads.
     */
    private const ICON_PATH = 'images/categories/icons';

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * CategoryService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads and deletions.
     */
    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated categories based on provided filters.
     *
     * Retrieves a paginated list of categories, applying scopes for searching,
     * status, featured, and date ranges. Includes parent and nested children as a tree.
     *
     * @param  array<string, mixed>  $filters  An associative array of filters (e.g., 'search', 'status', 'featured', 'parent_id', 'start_date', 'end_date').
     * @param  int  $perPage  The number of records to return per page.
     * @return LengthAwarePaginator A paginated collection of Category models.
     */
    public function getPaginatedCategories(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->with([
                'parent:id,name',
                'children' => $this->childrenTreeLoader(0),
            ])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Recursive eager loader for children (up to max depth).
     *
     * @param  int  $depth  Current depth in the tree.
     * @param  int  $maxDepth  Maximum depth to load (default 10).
     * @return Closure Closure to pass to with() for nested children.
     */
    private function childrenTreeLoader(int $depth, int $maxDepth = 10): Closure
    {
        return function ($query) use ($depth, $maxDepth) {
            if ($depth >= $maxDepth) {
                return;
            }

            $query->with([
                'children' => $this->childrenTreeLoader($depth + 1, $maxDepth),
            ]);
        };
    }

    /**
     * Get categories in a tree structure.
     *
     * Returns only root categories (parent_id null) with nested children,
     * filtered to active categories only, ordered by name.
     *
     * @return Collection A collection of root Category models with children loaded.
     */
    public function getCategoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with([
                'parent:id,name',
                'children' => $this->childrenTreeLoader(0),
            ])
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a lightweight list of active category options.
     *
     * Useful for populating frontend select dropdowns. Only fetches the `id` and `name` of active categories.
     *
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(): Collection
    {
        return Category::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Category $category) => [
                'value' => $category->id,
                'label' => $category->name,
            ]);
    }

    /**
     * Create a newly registered category.
     *
     * Processes file uploads if image or icon are provided and stores the new category record
     * within a database transaction to ensure data integrity.
     *
     * @param  array<string, mixed>  $data  The validated request data for the new category.
     * @return Category The newly created Category model instance.
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            return Category::query()->create($data);
        });
    }

    /**
     * Handle Image/Icon Upload via UploadService.
     *
     * Checks if an image or icon file is present in the data array. If so, it deletes the
     * old file (if updating) and uploads the new one, injecting the paths into the data array.
     *
     * @param  array<string, mixed>  $data  The input data potentially containing 'image' and/or 'icon' files.
     * @param  Category|null  $category  The existing category model if performing an update.
     * @return array<string, mixed> The modified data array with uploaded file paths.
     */
    private function handleUploads(array $data, ?Category $category = null): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($category?->image) {
                $this->uploadService->delete($category->image);
            }
            $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            if ($category?->icon) {
                $this->uploadService->delete($category->icon);
            }
            $path = $this->uploadService->upload($data['icon'], self::ICON_PATH);
            $data['icon'] = $path;
            $data['icon_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    /**
     * Reparent a category (change its parent_id).
     *
     * Prevents moving to self or to a descendant (would create a cycle).
     *
     * @param  Category  $category  The category model instance to reparent.
     * @param  int|null  $parentId  The new parent category ID, or null for root.
     * @return Category The freshly updated Category model instance.
     *
     * @throws ConflictHttpException If moving to self or to a descendant.
     */
    public function reparentCategory(Category $category, ?int $parentId): Category
    {
        if ($parentId === $category->id) {
            throw new ConflictHttpException('Cannot move category to itself.');
        }

        if ($parentId !== null && $this->isDescendantOf($category->id, $parentId)) {
            throw new ConflictHttpException('Cannot move category to a descendant (would create a cycle).');
        }

        $category->update(['parent_id' => $parentId]);

        return $category->fresh();
    }

    /**
     * Check if $descendantId is a descendant of $ancestorId in the category tree.
     *
     * @param  int  $ancestorId  The potential ancestor category ID.
     * @param  int  $descendantId  The potential descendant category ID.
     * @return bool True if descendantId is under ancestorId in the tree.
     */
    private function isDescendantOf(int $ancestorId, int $descendantId): bool
    {
        $current = Category::query()->find($descendantId);

        while ($current?->parent_id !== null) {
            if ($current->parent_id === $ancestorId) {
                return true;
            }
            $current = $current->parent;
        }

        return false;
    }

    /**
     * Update an existing category's information.
     *
     * Processes potential new image/icon uploads and updates the category record within
     * a database transaction.
     *
     * @param  Category  $category  The category model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Category The freshly updated Category model instance.
     */
    public function updateCategory(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $data = $this->handleUploads($data, $category);
            $category->update($data);

            return $category->fresh();
        });
    }

    /**
     * Delete a specific category.
     *
     * Deletes the category and its associated image/icon files. Will abort if the category
     * has child categories or is linked to any existing products.
     *
     * @param  Category  $category  The category model instance to delete.
     *
     * @throws ConflictHttpException If the category has children or associated products.
     */
    public function deleteCategory(Category $category): void
    {
        if ($category->children()->exists()) {
            throw new ConflictHttpException("Cannot delete category '{$category->name}' as it has child categories.");
        }

        if ($category->products()->exists()) {
            throw new ConflictHttpException("Cannot delete category '{$category->name}' as it has associated products.");
        }

        DB::transaction(function () use ($category) {
            $this->cleanupFiles($category);
            $category->delete();
        });
    }

    /**
     * Remove files associated with a category.
     *
     * @param  Category  $category  The category model whose files should be removed from storage.
     */
    private function cleanupFiles(Category $category): void
    {
        if ($category->image) {
            $this->uploadService->delete($category->image);
        }
        if ($category->icon) {
            $this->uploadService->delete($category->icon);
        }
    }

    /**
     * Bulk delete multiple categories.
     *
     * Iterates over an array of category IDs and attempts to delete them.
     * Skips any categories that have child categories or associated products to prevent database relationship errors.
     *
     * @param  array<int>  $ids  Array of category IDs to be deleted.
     * @return int The total count of successfully deleted categories.
     */
    public function bulkDeleteCategories(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $categories = Category::query()->whereIn('id', $ids)->withCount(['products', 'children'])->get();
            $count = 0;

            foreach ($categories as $category) {
                if ($category->products_count > 0 || $category->children_count > 0) {
                    continue;
                }

                $this->cleanupFiles($category);
                $category->delete();
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple categories.
     *
     * @param  array<int>  $ids  Array of category IDs to update.
     * @param  bool  $isActive  The new active status (true for active, false for inactive).
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Category::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Update the featured status for multiple categories.
     *
     * @param  array<int>  $ids  Array of category IDs to update.
     * @param  bool  $isFeatured  The new featured status (true for featured, false for not featured).
     * @return int The number of records updated.
     */
    public function bulkUpdateFeatured(array $ids, bool $isFeatured): int
    {
        return Category::query()->whereIn('id', $ids)->update(['featured' => $isFeatured]);
    }

    /**
     * Update the sync-disabled status for multiple categories.
     *
     * @param  array<int>  $ids  Array of category IDs to update.
     * @param  bool  $isSyncDisabled  The new sync-disabled status (true to disable sync, false to enable).
     * @return int The number of records updated.
     */
    public function bulkUpdateSync(array $ids, bool $isSyncDisabled): int
    {
        return Category::query()->whereIn('id', $ids)->update(['is_sync_disable' => $isSyncDisabled]);
    }

    /**
     * Import multiple categories from an uploaded Excel or CSV file.
     *
     * Uses Maatwebsite Excel to process the file in batches and chunks.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file containing category data.
     */
    public function importCategories(UploadedFile $file): void
    {
        ExcelFacade::import(new CategoriesImport, $file);
    }

    /**
     * Retrieve the path to the sample categories import template.
     *
     * @return string The absolute file path to the sample CSV.
     *
     * @throws RuntimeException If the template file does not exist on the server.
     */
    public function download(): string
    {
        $fileName = 'categories-sample.csv';

        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template categories not found.');
        }

        return $path;
    }

    /**
     * Generate an export file (Excel or PDF) containing category data.
     *
     * Compiles the requested category data into a file stored on the public disk.
     * Supports column selection, ID filtering, and date range filtering.
     *
     * @param  array<int>  $ids  Specific category IDs to export (leave empty to export all based on filters).
     * @param  string  $format  The file format requested ('excel' or 'pdf').
     * @param  array<string>  $columns  Specific column names to include in the export.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'categories_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CategoriesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
