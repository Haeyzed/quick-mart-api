<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CategoryBulkActionRequest;
use App\Http\Requests\Categories\ReparentCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CategoryResource;
use App\Mail\ExportMail;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CategoryController
 *
 * API Controller for Category CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to CategoryService.
 *
 * @tags Category Management
 */
class CategoryController extends Controller
{
    /**
     * CategoryController constructor.
     */
    public function __construct(
        private readonly CategoryService $service
    ) {}

    /**
     * List Categories
     *
     * Display a paginated listing of categories. Supports searching and filtering by status, featured, parent, and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories list.');
        }

        $categories = $this->service->getPaginatedCategories(
            $request->validate([
                /**
                 * Search term to filter categories by name or slug.
                 *
                 * @example "Electronics"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'status' => ['nullable', 'boolean'],
                /**
                 * Filter by featured status.
                 *
                 * @example true
                 */
                'featured' => ['nullable', 'boolean'],
                /**
                 * Filter by parent category ID.
                 *
                 * @example 1
                 */
                'parent_id' => ['nullable', 'integer', 'exists:categories,id'],
                /**
                 * Filter categories starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter categories up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            /**
             * Amount of items per page.
             *
             * @example 50
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Get Category Options
     *
     * Retrieve a simplified list of active categories for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories options.');
        }

        return response()->success($this->service->getOptions(), 'Category options retrieved successfully');
    }

    /**
     * Category Tree
     *
     * Display a tree-view listing of root categories with nested children (active only).
     */
    public function tree(): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories tree.');
        }

        $tree = $this->service->getCategoryTree();

        return response()->success(
            CategoryResource::collection($tree),
            'Category tree retrieved successfully'
        );
    }

    /**
     * Create Category
     *
     * Store a newly created category in the system.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create categories')) {
            return response()->forbidden('Permission denied for create category.');
        }

        $category = $this->service->createCategory($request->validated());

        return response()->success(
            new CategoryResource($category),
            'Category created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Category
     *
     * Retrieve the details of a specific category by its ID.
     */
    public function show(Category $category): JsonResponse
    {
        if (auth()->user()->denies('view category details')) {
            return response()->forbidden('Permission denied for view category.');
        }

        return response()->success(
            new CategoryResource($category),
            'Category details retrieved successfully'
        );
    }

    /**
     * Update Category
     *
     * Update the specified category's information.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for update category.');
        }

        $updatedCategory = $this->service->updateCategory($category, $request->validated());

        return response()->success(
            new CategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Reparent Category
     *
     * Update the category's parent (e.g. for drag-and-drop tree reordering).
     */
    public function reparent(ReparentCategoryRequest $request, Category $category): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for updating categories.');
        }

        $updatedCategory = $this->service->reparentCategory(
            $category,
            $request->validated()['parent_id']
        );

        return response()->success(
            new CategoryResource($updatedCategory),
            'Category reparented successfully'
        );
    }

    /**
     * Delete Category
     *
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): JsonResponse
    {
        if (auth()->user()->denies('delete categories')) {
            return response()->forbidden('Permission denied for delete category.');
        }

        $this->service->deleteCategory($category);

        return response()->success(null, 'Category deleted successfully');
    }

    /**
     * Bulk Delete Categories
     *
     * Delete multiple categories simultaneously using an array of IDs.
     */
    public function bulkDestroy(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete categories')) {
            return response()->forbidden('Permission denied for bulk delete categories.');
        }

        $count = $this->service->bulkDeleteCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} categories"
        );
    }

    /**
     * Bulk Activate Categories
     *
     * Set the active status of multiple categories to true.
     */
    public function bulkActivate(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} categories activated"
        );
    }

    /**
     * Bulk Deactivate Categories
     *
     * Set the active status of multiple categories to false.
     */
    public function bulkDeactivate(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} categories deactivated"
        );
    }

    /**
     * Bulk Enable Featured
     *
     * Set the featured status of multiple categories to true.
     */
    public function bulkEnableFeatured(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateFeatured($request->validated()['ids'], true);

        return response()->success(
            ['updated_count' => $count],
            "Enabled featured for {$count} categories"
        );
    }

    /**
     * Bulk Disable Featured
     *
     * Set the featured status of multiple categories to false.
     */
    public function bulkDisableFeatured(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateFeatured($request->validated()['ids'], false);

        return response()->success(
            ['updated_count' => $count],
            "Disabled featured for {$count} categories"
        );
    }

    /**
     * Bulk Enable Sync
     *
     * Set the sync-disabled status of multiple categories to false (sync enabled).
     */
    public function bulkEnableSync(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateSync($request->validated()['ids'], false);

        return response()->success(
            ['updated_count' => $count],
            "Enabled sync for {$count} categories"
        );
    }

    /**
     * Bulk Disable Sync
     *
     * Set the sync-disabled status of multiple categories to true (sync disabled).
     */
    public function bulkDisableSync(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateSync($request->validated()['ids'], true);

        return response()->success(
            ['updated_count' => $count],
            "Disabled sync for {$count} categories"
        );
    }

    /**
     * Import Categories
     *
     * Import multiple categories into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import categories')) {
            return response()->forbidden('Permission denied for import categories.');
        }

        $this->service->importCategories($request->file('file'));

        return response()->success(null, 'Categories imported successfully');
    }

    /**
     * Export Categories
     *
     * Export a list of categories to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export categories')) {
            return response()->forbidden('Permission denied for export categories.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        // 3. Handle Email Method
        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'categories_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Categories Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import categories')) {
            return response()->forbidden('Permission denied for downloading categories import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
