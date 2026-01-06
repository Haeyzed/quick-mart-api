<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeCategoryBulkDestroyRequest;
use App\Http\Requests\IncomeCategoryIndexRequest;
use App\Http\Requests\IncomeCategoryRequest;
use App\Http\Resources\IncomeCategoryResource;
use App\Models\IncomeCategory;
use App\Services\IncomeCategoryService;
use Illuminate\Http\JsonResponse;

/**
 * IncomeCategoryController
 *
 * API controller for managing income categories with full CRUD operations.
 */
class IncomeCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param IncomeCategoryService $service
     */
    public function __construct(
        private readonly IncomeCategoryService $service
    )
    {
    }

    /**
     * Display a paginated listing of income categories.
     *
     * @param IncomeCategoryIndexRequest $request
     * @return JsonResponse
     */
    public function index(IncomeCategoryIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $incomeCategories = $this->service->getIncomeCategories($filters, $perPage)
            ->through(fn($category) => new IncomeCategoryResource($category));

        return response()->success($incomeCategories, 'Income categories fetched successfully');
    }

    /**
     * Store a newly created income category.
     *
     * @param IncomeCategoryRequest $request
     * @return JsonResponse
     */
    public function store(IncomeCategoryRequest $request): JsonResponse
    {
        $incomeCategory = $this->service->createIncomeCategory($request->validated());

        return response()->success(
            new IncomeCategoryResource($incomeCategory),
            'Income category created successfully',
            201
        );
    }

    /**
     * Display the specified income category.
     *
     * @param IncomeCategory $incomeCategory
     * @return JsonResponse
     */
    public function show(IncomeCategory $incomeCategory): JsonResponse
    {
        return response()->success(
            new IncomeCategoryResource($incomeCategory),
            'Income category retrieved successfully'
        );
    }

    /**
     * Update the specified income category.
     *
     * @param IncomeCategoryRequest $request
     * @param IncomeCategory $incomeCategory
     * @return JsonResponse
     */
    public function update(IncomeCategoryRequest $request, IncomeCategory $incomeCategory): JsonResponse
    {
        $incomeCategory = $this->service->updateIncomeCategory($incomeCategory, $request->validated());

        return response()->success(
            new IncomeCategoryResource($incomeCategory),
            'Income category updated successfully'
        );
    }

    /**
     * Remove the specified income category from storage.
     *
     * @param IncomeCategory $incomeCategory
     * @return JsonResponse
     */
    public function destroy(IncomeCategory $incomeCategory): JsonResponse
    {
        $this->service->deleteIncomeCategory($incomeCategory);

        return response()->success(null, 'Income category deleted successfully');
    }

    /**
     * Bulk delete multiple income categories.
     *
     * @param IncomeCategoryBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(IncomeCategoryBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteIncomeCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} income categories successfully"
        );
    }

    /**
     * Generate a unique code for income category.
     *
     * @return JsonResponse
     */
    public function generateCode(): JsonResponse
    {
        $code = IncomeCategory::generateCode();

        return response()->success(
            ['code' => $code],
            'Income category code generated successfully'
        );
    }
}
