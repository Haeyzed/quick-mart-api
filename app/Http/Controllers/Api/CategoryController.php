<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CategoryBulkDestroyRequest;
use App\Http\Requests\Categories\CategoryBulkUpdateRequest;
use App\Http\Requests\Categories\CategoryIndexRequest;
use App\Http\Requests\Categories\CategoryRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

/**
 * CategoryController
 *
 * API controller for managing categories with full CRUD operations.
 */
class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CategoryService $service
     */
    public function __construct(
        private readonly CategoryService $service
    )
    {
    }

    /**
     * Display a paginated listing of categories.
     *
     * @param CategoryIndexRequest $request
     * @return JsonResponse
     */
    public function index(CategoryIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $categories = $this->service->getCategories($filters, $perPage)
            ->through(fn($category) => new CategoryResource($category));

        return response()->success($categories, 'Categories fetched successfully');
    }

    /**
     * Store a newly created category.
     *
     * @param CategoryRequest $request
     * @return JsonResponse
     */
    public function store(CategoryRequest $request): JsonResponse
    {
        $category = $this->service->createCategory($request->validated());

        return response()->success(
            new CategoryResource($category),
            'Category created successfully',
            201
        );
    }

    /**
     * Display the specified category.
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function show(Category $category): JsonResponse
    {
        return response()->success(
            new CategoryResource($category),
            'Category retrieved successfully'
        );
    }

    /**
     * Update the specified category.
     *
     * @param CategoryRequest $request
     * @param Category $category
     * @return JsonResponse
     */
    public function update(CategoryRequest $request, Category $category): JsonResponse
    {
        $category = $this->service->updateCategory($category, $request->validated());

        return response()->success(
            new CategoryResource($category),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified category from storage.
     *
     * @param Category $category
     * @return JsonResponse
     */
    public function destroy(Category $category): JsonResponse
    {
        $this->service->deleteCategory($category);

        return response()->success(null, 'Category deleted successfully');
    }

    /**
     * Bulk delete multiple categories.
     *
     * @param CategoryBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(CategoryBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} categories successfully"
        );
    }

    /**
     * Import categories from a file.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importCategories($request->file('file'));

        return response()->success(null, 'Categories imported successfully');
    }

    /**
     * Bulk activate multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateCategories($request->validated()['ids']);

        return response()->success(
            ['activated_count' => $count],
            "Activated {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Bulk deactivate multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateCategories($request->validated()['ids']);

        return response()->success(
            ['deactivated_count' => $count],
            "Deactivated {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Bulk enable featured status for multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkEnableFeatured(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkEnableFeatured($request->validated()['ids']);

        return response()->success(
            ['updated_count' => $count],
            "Enabled featured for {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Bulk disable featured status for multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDisableFeatured(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDisableFeatured($request->validated()['ids']);

        return response()->success(
            ['updated_count' => $count],
            "Disabled featured for {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Bulk enable sync for multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkEnableSync(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkEnableSync($request->validated()['ids']);

        return response()->success(
            ['updated_count' => $count],
            "Enabled sync for {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Bulk disable sync for multiple categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDisableSync(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDisableSync($request->validated()['ids']);

        return response()->success(
            ['updated_count' => $count],
            "Disabled sync for {$count} categor" . ($count !== 1 ? 'ies' : 'y') . " successfully"
        );
    }

    /**
     * Export categories to Excel or PDF.
     *
     * @param ExportRequest $request
     * @return JsonResponse|Response
     */
    public function export(ExportRequest $request): JsonResponse|Response
    {
        $validated = $request->validated();
        $ids = $validated['ids'] ?? [];
        $format = $validated['format'];
        $method = $validated['method'];
        $columns = $validated['columns'] ?? [];
        $user = $method === 'email' ? User::findOrFail($validated['user_id']) : null;

        $filePath = $this->service->exportCategories($ids, $format, $user, $columns, $method);

        if ($method === 'download') {
            // For download, return file response (frontend handles blob download)
            // File will be temporarily stored and can be cleaned up by a scheduled job if needed
            return Storage::disk('public')->download($filePath);
        }

        // For email, file is stored and attached to email (no need to return file)
        return response()->success(null, 'Export file sent via email successfully');
    }
}

