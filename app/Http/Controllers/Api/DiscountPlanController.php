<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DiscountPlanBulkDestroyRequest;
use App\Http\Requests\DiscountPlanIndexRequest;
use App\Http\Requests\DiscountPlanRequest;
use App\Http\Resources\DiscountPlanResource;
use App\Models\DiscountPlan;
use App\Services\DiscountPlanService;
use Illuminate\Http\JsonResponse;

/**
 * DiscountPlanController
 *
 * API controller for managing discount plans with full CRUD operations.
 */
class DiscountPlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DiscountPlanService $service
     */
    public function __construct(
        private readonly DiscountPlanService $service
    )
    {
    }

    /**
     * Display a paginated listing of discount plans.
     *
     * @param DiscountPlanIndexRequest $request
     * @return JsonResponse
     */
    public function index(DiscountPlanIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $discountPlans = $this->service->getDiscountPlans($filters, $perPage)
            ->through(fn($discountPlan) => new DiscountPlanResource($discountPlan));

        return response()->success($discountPlans, 'Discount plans fetched successfully');
    }

    /**
     * Store a newly created discount plan.
     *
     * @param DiscountPlanRequest $request
     * @return JsonResponse
     */
    public function store(DiscountPlanRequest $request): JsonResponse
    {
        $discountPlan = $this->service->createDiscountPlan($request->validated());

        return response()->success(
            new DiscountPlanResource($discountPlan),
            'Discount plan created successfully',
            201
        );
    }

    /**
     * Display the specified discount plan.
     *
     * @param DiscountPlan $discountPlan
     * @return JsonResponse
     */
    public function show(DiscountPlan $discountPlan): JsonResponse
    {
        return response()->success(
            new DiscountPlanResource($discountPlan),
            'Discount plan retrieved successfully'
        );
    }

    /**
     * Update the specified discount plan.
     *
     * @param DiscountPlanRequest $request
     * @param DiscountPlan $discountPlan
     * @return JsonResponse
     */
    public function update(DiscountPlanRequest $request, DiscountPlan $discountPlan): JsonResponse
    {
        $discountPlan = $this->service->updateDiscountPlan($discountPlan, $request->validated());

        return response()->success(
            new DiscountPlanResource($discountPlan),
            'Discount plan updated successfully'
        );
    }

    /**
     * Remove the specified discount plan from storage.
     *
     * @param DiscountPlan $discountPlan
     * @return JsonResponse
     */
    public function destroy(DiscountPlan $discountPlan): JsonResponse
    {
        $this->service->deleteDiscountPlan($discountPlan);

        return response()->success(null, 'Discount plan deleted successfully');
    }

    /**
     * Bulk delete multiple discount plans.
     *
     * @param DiscountPlanBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DiscountPlanBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteDiscountPlans($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} discount plans successfully"
        );
    }
}
