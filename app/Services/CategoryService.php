<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CategoriesExport;
use App\Imports\CategoriesImport;
use App\Models\Category;
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
 * Handles business logic for Categories.
 */
class CategoryService
{
    private const IMAGE_PATH = 'images/categories';

    private const ICON_PATH = 'images/categories/icons';

    private const TEMPLATE_PATH = 'Imports/Templates';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated categories based on filters.
     * Includes children as a nested tree for each category.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCategories(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->with(['parent:id,name', 'children'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get categories in a tree structure.
     */
    public function getCategoryTree(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->with('children')
            ->active()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get list of potential parent categories.
     * Returns value/label format for select/combobox components.
     *
     * @return \Illuminate\Support\Collection<int, array{value: int, label: string}>
     */
    public function getParentOptions(): \Illuminate\Support\Collection
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
     * Create a new category.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCategory(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $data = $this->handleUploads($data);

            return Category::query()->create($data);
        });
    }

    /**
     * Update an existing category.
     *
     * @param  array<string, mixed>  $data
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
     * Delete a category.
     *
     * @throws ConflictHttpException
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
     * Bulk delete categories.
     *
     * @param  array<int>  $ids
     * @return int Count of deleted items.
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
     * Bulk update status.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Category::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Bulk update featured status.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateFeatured(array $ids, bool $isFeatured): int
    {
        return Category::query()->whereIn('id', $ids)->update(['featured' => $isFeatured]);
    }

    /**
     * Bulk update sync status.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateSync(array $ids, bool $isSyncDisabled): int
    {
        return Category::query()->whereIn('id', $ids)->update(['is_sync_disable' => $isSyncDisabled]);
    }

    /**
     * Import categories from file.
     */
    public function importCategories(UploadedFile $file): void
    {
        ExcelFacade::import(new CategoriesImport, $file);
    }

    /**
     * Download a categories CSV template.
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
     * Export categories to file.
     *
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     * @return string Relative file path.
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

    /**
     * Handle Image/Icon Upload via UploadService.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function handleUploads(array $data, ?Category $category = null): array
    {
        // Handle Image
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            if ($category?->image) {
                $this->uploadService->delete($category->image);
            }
            $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        // Handle Icon
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
     * Remove associated files.
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
}
