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
use App\Http\Resources\CategoryOptionResource;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class CategoryController
 *
 * API controller for managing categories.
 * Delegates business logic to CategoryService.
 */
class CategoryController extends Controller
{
    /**
     * @param CategoryService $service
     */
    public function __construct(
        private readonly CategoryService $service
    ) {}

    /**
     * Get parent category options for select inputs.
     *
     * @return JsonResponse
     */
    public function parents(): JsonResponse
    {
        $categories = $this->service->getParentCategoriesForSelect();

        return response()->success(
            CategoryOptionResource::collection($categories),
            'Parent categories fetched successfully'
        );
    }

    /**
     * Display a paginated listing of categories.
     *
     * @param CategoryIndexRequest $request
     * @return JsonResponse
     */
    public function index(CategoryIndexRequest $request): JsonResponse
    {
        $categories = $this->service->getCategories(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        // Transform collection while keeping pagination metadata for the Response Macro
        $categories->through(fn (Category $category) => new CategoryResource($category));

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
            Response::HTTP_CREATED
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
        $updatedCategory = $this->service->updateCategory($category, $request->validated());

        return response()->success(
            new CategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Remove the specified category.
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
     * Bulk delete categories.
     *
     * @param CategoryBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(CategoryBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} " . str('category')->plural($count)
        );
    }

    /**
     * Bulk activate categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateCategories($request->validated()['ids']);
        return response()->success(['activated_count' => $count], "{$count} categories activated");
    }

    /**
     * Bulk deactivate categories.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateCategories($request->validated()['ids']);
        return response()->success(['deactivated_count' => $count], "{$count} categories deactivated");
    }

    /**
     * Bulk enable featured status.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkEnableFeatured(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkEnableFeatured($request->validated()['ids']);
        return response()->success(['updated_count' => $count], "Enabled featured for {$count} categories");
    }

    /**
     * Bulk disable featured status.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDisableFeatured(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDisableFeatured($request->validated()['ids']);
        return response()->success(['updated_count' => $count], "Disabled featured for {$count} categories");
    }

    /**
     * Bulk enable sync.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkEnableSync(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkEnableSync($request->validated()['ids']);
        return response()->success(['updated_count' => $count], "Enabled sync for {$count} categories");
    }

    /**
     * Bulk disable sync.
     *
     * @param CategoryBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDisableSync(CategoryBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDisableSync($request->validated()['ids']);
        return response()->success(['updated_count' => $count], "Disabled sync for {$count} categories");
    }

    /**
     * Import categories.
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
     * Export categories.
     *
     * @param ExportRequest $request
     * @return JsonResponse|BinaryFileResponse
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();
        
        $user = ($validated['method'] === 'email') 
            ? User::findOrFail($validated['user_id']) 
            : null;

        $filePath = $this->service->exportCategories(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(
                Storage::disk('public')->path($filePath)
            );
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}