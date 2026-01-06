<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IncomeBulkDestroyRequest;
use App\Http\Requests\IncomeIndexRequest;
use App\Http\Requests\IncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;

/**
 * IncomeController
 *
 * API controller for managing incomes with full CRUD operations.
 */
class IncomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param IncomeService $service
     */
    public function __construct(
        private readonly IncomeService $service
    )
    {
    }

    /**
     * Display a paginated listing of incomes.
     *
     * @param IncomeIndexRequest $request
     * @return JsonResponse
     */
    public function index(IncomeIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $incomes = $this->service->getIncomes($filters, $perPage)
            ->through(fn($income) => new IncomeResource($income));

        return response()->success($incomes, 'Incomes fetched successfully');
    }

    /**
     * Store a newly created income.
     *
     * @param IncomeRequest $request
     * @return JsonResponse
     */
    public function store(IncomeRequest $request): JsonResponse
    {
        $income = $this->service->createIncome($request->validated());

        return response()->success(
            new IncomeResource($income),
            'Income created successfully',
            201
        );
    }

    /**
     * Display the specified income.
     *
     * @param Income $income
     * @return JsonResponse
     */
    public function show(Income $income): JsonResponse
    {
        $income = $this->service->getIncome($income->id);

        return response()->success(
            new IncomeResource($income),
            'Income retrieved successfully'
        );
    }

    /**
     * Update the specified income.
     *
     * @param IncomeRequest $request
     * @param Income $income
     * @return JsonResponse
     */
    public function update(IncomeRequest $request, Income $income): JsonResponse
    {
        $income = $this->service->updateIncome($income, $request->validated());

        return response()->success(
            new IncomeResource($income),
            'Income updated successfully'
        );
    }

    /**
     * Remove the specified income from storage.
     *
     * @param Income $income
     * @return JsonResponse
     */
    public function destroy(Income $income): JsonResponse
    {
        $this->service->deleteIncome($income);

        return response()->success(null, 'Income deleted successfully');
    }

    /**
     * Bulk delete multiple incomes.
     *
     * @param IncomeBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(IncomeBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteIncomes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} incomes successfully"
        );
    }
}

