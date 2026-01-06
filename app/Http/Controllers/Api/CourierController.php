<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CourierBulkDestroyRequest;
use App\Http\Requests\CourierIndexRequest;
use App\Http\Requests\CourierRequest;
use App\Http\Resources\CourierResource;
use App\Models\Courier;
use App\Services\CourierService;
use Illuminate\Http\JsonResponse;

/**
 * CourierController
 *
 * API controller for managing couriers with full CRUD operations.
 */
class CourierController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CourierService $service
     */
    public function __construct(
        private readonly CourierService $service
    )
    {
    }

    /**
     * Display a paginated listing of couriers.
     *
     * @param CourierIndexRequest $request
     * @return JsonResponse
     */
    public function index(CourierIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $couriers = $this->service->getCouriers($filters, $perPage)
            ->through(fn($courier) => new CourierResource($courier));

        return response()->success($couriers, 'Couriers fetched successfully');
    }

    /**
     * Store a newly created courier.
     *
     * @param CourierRequest $request
     * @return JsonResponse
     */
    public function store(CourierRequest $request): JsonResponse
    {
        $courier = $this->service->createCourier($request->validated());

        return response()->success(
            new CourierResource($courier),
            'Courier created successfully',
            201
        );
    }

    /**
     * Display the specified courier.
     *
     * @param Courier $courier
     * @return JsonResponse
     */
    public function show(Courier $courier): JsonResponse
    {
        return response()->success(
            new CourierResource($courier),
            'Courier retrieved successfully'
        );
    }

    /**
     * Update the specified courier.
     *
     * @param CourierRequest $request
     * @param Courier $courier
     * @return JsonResponse
     */
    public function update(CourierRequest $request, Courier $courier): JsonResponse
    {
        $courier = $this->service->updateCourier($courier, $request->validated());

        return response()->success(
            new CourierResource($courier),
            'Courier updated successfully'
        );
    }

    /**
     * Remove the specified courier from storage.
     *
     * @param Courier $courier
     * @return JsonResponse
     */
    public function destroy(Courier $courier): JsonResponse
    {
        $this->service->deleteCourier($courier);

        return response()->success(null, 'Courier deleted successfully');
    }

    /**
     * Bulk delete multiple couriers.
     *
     * @param CourierBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(CourierBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCouriers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} couriers successfully"
        );
    }
}
