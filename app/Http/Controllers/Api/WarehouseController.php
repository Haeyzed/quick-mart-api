<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\WarehouseBulkDestroyRequest;
use App\Http\Requests\WarehouseIndexRequest;
use App\Http\Requests\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;

/**
 * WarehouseController
 *
 * Handles HTTP requests for warehouse management operations.
 * Provides RESTful API endpoints for CRUD operations and bulk deletion.
 */
class WarehouseController extends Controller
{
    public function __construct(
        private readonly WarehouseService $warehouseService
    )
    {
    }

    /**
     * Display a listing of warehouses.
     *
     * @param WarehouseIndexRequest $request
     * @return JsonResponse
     */
    public function index(WarehouseIndexRequest $request): JsonResponse
    {
        $perPage = (int)($request->validated()['per_page'] ?? 10);
        $filters = $request->only(['is_active', 'search']);

        $warehouses = $this->warehouseService->getWarehouses($filters, $perPage);

        return response()->success(
            WarehouseResource::collection($warehouses),
            'Warehouses retrieved successfully'
        );
    }

    /**
     * Store a newly created warehouse.
     *
     * @param WarehouseRequest $request
     * @return JsonResponse
     */
    public function store(WarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->warehouseService->createWarehouse($request->validated());

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse created successfully',
            201
        );
    }

    /**
     * Display the specified warehouse.
     *
     * @param Warehouse $warehouse
     * @return JsonResponse
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse retrieved successfully'
        );
    }

    /**
     * Update the specified warehouse.
     *
     * @param WarehouseRequest $request
     * @param Warehouse $warehouse
     * @return JsonResponse
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $warehouse = $this->warehouseService->updateWarehouse($warehouse, $request->validated());

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse updated successfully'
        );
    }

    /**
     * Remove the specified warehouse.
     *
     * @param Warehouse $warehouse
     * @return JsonResponse
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->warehouseService->deleteWarehouse($warehouse);

        return response()->success(
            null,
            'Warehouse deleted successfully'
        );
    }

    /**
     * Bulk delete multiple warehouses.
     *
     * @param WarehouseBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(WarehouseBulkDestroyRequest $request): JsonResponse
    {
        $ids = $request->validated()['ids'];
        $deletedCount = $this->warehouseService->bulkDeleteWarehouses($ids);

        return response()->success(
            ['deleted_count' => $deletedCount],
            "Successfully deleted {$deletedCount} warehouse(s)"
        );
    }

    /**
     * Import warehouses from a file.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->warehouseService->importWarehouses($request->file('file'));

        return response()->success(null, 'Warehouses imported successfully');
    }

    /**
     * Get all active warehouses.
     *
     * @return JsonResponse
     */
    public function getAllActive(): JsonResponse
    {
        $warehouses = $this->warehouseService->getAllActive();

        return response()->success(
            WarehouseResource::collection($warehouses),
            'Active warehouses retrieved successfully'
        );
    }
}

