<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExpenseCategoryBulkDestroyRequest;
use App\Http\Requests\ExpenseCategoryIndexRequest;
use App\Http\Requests\ExpenseCategoryRequest;
use App\Http\Resources\ExpenseCategoryResource;
use App\Models\ExpenseCategory;
use App\Services\ExpenseCategoryService;
use Illuminate\Http\JsonResponse;

/**
 * ExpenseCategoryController
 *
 * API controller for managing expense categories with full CRUD operations.
 */
class ExpenseCategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ExpenseCategoryService $service
     */
    public function __construct(
        private readonly ExpenseCategoryService $service
    )
    {
    }

    /**
     * Display a paginated listing of expense categories.
     *
     * @param ExpenseCategoryIndexRequest $request
     * @return JsonResponse
     */
    public function index(ExpenseCategoryIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $expenseCategories = $this->service->getExpenseCategories($filters, $perPage)
            ->through(fn($category) => new ExpenseCategoryResource($category));

        return response()->success($expenseCategories, 'Expense categories fetched successfully');
    }

    /**
     * Store a newly created expense category.
     *
     * @param ExpenseCategoryRequest $request
     * @return JsonResponse
     */
    public function store(ExpenseCategoryRequest $request): JsonResponse
    {
        $expenseCategory = $this->service->createExpenseCategory($request->validated());

        return response()->success(
            new ExpenseCategoryResource($expenseCategory),
            'Expense category created successfully',
            201
        );
    }

    /**
     * Display the specified expense category.
     *
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function show(ExpenseCategory $expenseCategory): JsonResponse
    {
        return response()->success(
            new ExpenseCategoryResource($expenseCategory),
            'Expense category retrieved successfully'
        );
    }

    /**
     * Update the specified expense category.
     *
     * @param ExpenseCategoryRequest $request
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function update(ExpenseCategoryRequest $request, ExpenseCategory $expenseCategory): JsonResponse
    {
        $expenseCategory = $this->service->updateExpenseCategory($expenseCategory, $request->validated());

        return response()->success(
            new ExpenseCategoryResource($expenseCategory),
            'Expense category updated successfully'
        );
    }

    /**
     * Remove the specified expense category from storage.
     *
     * @param ExpenseCategory $expenseCategory
     * @return JsonResponse
     */
    public function destroy(ExpenseCategory $expenseCategory): JsonResponse
    {
        $this->service->deleteExpenseCategory($expenseCategory);

        return response()->success(null, 'Expense category deleted successfully');
    }

    /**
     * Bulk delete multiple expense categories.
     *
     * @param ExpenseCategoryBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(ExpenseCategoryBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteExpenseCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} expense categories successfully"
        );
    }
}
