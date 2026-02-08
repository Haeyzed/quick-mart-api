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
 * Class CategoryService
 *
 * Encapsulates all business logic for category operations.
 */
class CategoryService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    /**
     * Get paginated categories with filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCategories(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('categories-index');

        return Category::query()
            ->with('parent:id,name')
            ->when(isset($filters['status']), fn ($q) => 
                $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(isset($filters['featured_status']), fn ($q) => 
                $q->where('featured', $filters['featured_status'] === 'featured')
            )
            ->when(isset($filters['sync_status']), fn ($q) => 
                $q->where('is_sync_disable', $filters['sync_status'] === 'disabled')
            )
            ->when(isset($filters['parent_id']), fn ($q) => 
                $q->where('parent_id', $filters['parent_id'])
            )
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($sub) => $sub
                    ->where('name', 'like', $term)
                    ->orWhere('short_description', 'like', $term)
                    ->orWhere('slug', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get parent options for select box (ID & Name only).
     *
     * @return Collection
     */
    public function getParentCategoriesForSelect(): Collection
    {
        $this->requirePermission('categories-index');

        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->select(['id', 'name'])
            ->get();
    }

    /**
     * Create a new category.
     *
     * @param array<string, mixed> $data
     * @return Category
     */
    public function createCategory(array $data): Category
    {
        $this->requirePermission('categories-create');

        return DB::transaction(function () use ($data) {
            $data = $this->handleFileUploads($data);

            // Defaults
            $data['is_active'] = $data['is_active'] ?? true;
            $data['featured'] = $data['featured'] ?? false;
            
            return Category::create($data);
        });
    }

    /**
     * Update an existing category.
     *
     * @param Category $category
     * @param array<string, mixed> $data
     * @return Category
     */
    public function updateCategory(Category $category, array $data): Category
    {
        $this->requirePermission('categories-update');

        return DB::transaction(function () use ($category, $data) {
            // Clean up old files if new ones are uploaded
            if (isset($data['image']) && $data['image'] instanceof UploadedFile && $category->image) {
                $this->uploadService->delete($category->image);
            }
            if (isset($data['icon']) && $data['icon'] instanceof UploadedFile && $category->icon) {
                $this->uploadService->delete($category->icon);
            }

            $data = $this->handleFileUploads($data);

            $category->update($data);
            return $category->fresh();
        });
    }

    /**
     * Delete a category.
     *
     * @param Category $category
     * @throws UnprocessableEntityHttpException
     */
    public function deleteCategory(Category $category): void
    {
        $this->requirePermission('categories-delete');

        if ($category->children()->exists()) {
            throw new UnprocessableEntityHttpException("Cannot delete category: it has child categories.");
        }

        if ($category->products()->exists()) {
            throw new UnprocessableEntityHttpException("Cannot delete category: it has associated products.");
        }

        DB::transaction(function () use ($category) {
            $this->cleanupFiles($category);
            $category->delete();
        });
    }

    /**
     * Bulk delete categories efficiently.
     * Only deletes categories that have no children and no products.
     *
     * @param array<int> $ids
     * @return int Count of deleted categories.
     */
    public function bulkDeleteCategories(array $ids): int
    {
        $this->requirePermission('categories-delete');

        return DB::transaction(function () use ($ids) {
            $categories = Category::whereIn('id', $ids)
                ->withCount(['products', 'children'])
                ->get();

            $deletedCount = 0;

            foreach ($categories as $category) {
                if ($category->products_count === 0 && $category->children_count === 0) {
                    $this->cleanupFiles($category);
                    $category->delete();
                    $deletedCount++;
                }
            }
            return $deletedCount;
        });
    }

    /**
     * Helper to clean up files.
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
     * Helper to handle file uploads.
     */
    private function handleFileUploads(array $data): array
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $path = $this->uploadService->upload($data['image'], config('storage.categories.images', 'categories'));
            $data['image'] = $path;
            $data['image_url'] = $this->uploadService->url($path);
        }

        if (isset($data['icon']) && $data['icon'] instanceof UploadedFile) {
            $path = $this->uploadService->upload($data['icon'], config('storage.categories.icons', 'categories/icons'));
            $data['icon'] = $path;
            $data['icon_url'] = $this->uploadService->url($path);
        }

        return $data;
    }

    // --- Bulk Updates ---

    public function bulkActivateCategories(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['is_active' => true]);
    }

    public function bulkDeactivateCategories(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['is_active' => false]);
    }

    public function bulkEnableFeatured(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['featured' => true]);
    }

    public function bulkDisableFeatured(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['featured' => false]);
    }

    public function bulkEnableSync(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['is_sync_disable' => false]);
    }

    public function bulkDisableSync(array $ids): int
    {
        $this->requirePermission('categories-update');
        return Category::whereIn('id', $ids)->update(['is_sync_disable' => true]);
    }

    // --- Import / Export ---

    public function importCategories(UploadedFile $file): void
    {
        $this->requirePermission('categories-import');
        Excel::import(new CategoriesImport(), $file);
    }

    public function exportCategories(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('categories-export');

        $fileName = 'categories_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $path = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new CategoriesExport($ids, $columns), $path, 'public');
        } else {
            $categories = Category::query()
                ->with('parent:id,name')
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->get();

            $pdf = PDF::loadView('exports.categories-pdf', compact('categories', 'columns'));
            Storage::disk('public')->put($path, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $path, $fileName);
        }

        return $path;
    }

    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::latest()->firstOr(fn() => throw new Exception("Mail settings not configured."));
        $generalSetting = GeneralSetting::latest()->first();

        $this->setMailInfo($mailSetting);
        
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Categories List', $generalSetting)
        );
    }
}