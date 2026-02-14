<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Warehouses\WarehouseBulkDestroyRequest;
use App\Http\Requests\Warehouses\WarehouseBulkUpdateRequest;
use App\Http\Requests\Warehouses\WarehouseIndexRequest;
use App\Http\Requests\Warehouses\WarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Warehouse CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, and getAllActive. All responses use the ResponseServiceProvider macros.
 *
 * @group Warehouse Management
 */
class WarehouseController extends Controller
{
    /**
     * WarehouseController constructor.
     *
     * @param WarehouseService $service
     */
    public function __construct(
        private readonly WarehouseService $service
    )
    {
    }

    /**
     * Display a paginated listing of warehouses.
     *
     * @param WarehouseIndexRequest $request Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated warehouses with meta and links.
     */
    public function index(WarehouseIndexRequest $request): JsonResponse
    {
        $warehouses = $this->service->getWarehouses(
            $request->validated(),
            (int)$request->input('per_page', 10)
        );

        $warehouses->through(fn(Warehouse $warehouse) => new WarehouseResource($warehouse));

        return response()->success(
            $warehouses,
            'Warehouses fetched successfully'
        );
    }

    /**
     * Store a newly created warehouse.
     *
     * @param WarehouseRequest $request Validated warehouse attributes.
     * @return JsonResponse Created warehouse with 201 status.
     */
    public function store(WarehouseRequest $request): JsonResponse
    {
        $warehouse = $this->service->createWarehouse($request->validated());

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified warehouse.
     *
     * @param Warehouse $warehouse The warehouse instance resolved via route model binding.
     * @return JsonResponse
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse = $this->service->getWarehouse($warehouse);

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse retrieved successfully'
        );
    }

    /**
     * Update the specified warehouse.
     *
     * @param WarehouseRequest $request Validated warehouse attributes.
     * @param Warehouse $warehouse The warehouse instance to update.
     * @return JsonResponse Updated warehouse.
     */
    public function update(WarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $updatedWarehouse = $this->service->updateWarehouse($warehouse, $request->validated());

        return response()->success(
            new WarehouseResource($updatedWarehouse),
            'Warehouse updated successfully'
        );
    }

    /**
     * Remove the specified warehouse (deactivates it).
     *
     * @param Warehouse $warehouse The warehouse instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->service->deleteWarehouse($warehouse);

        return response()->success(null, 'Warehouse deleted successfully');
    }

    /**
     * Bulk delete warehouses (deactivates them).
     *
     * @param WarehouseBulkDestroyRequest $request Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(WarehouseBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteWarehouses($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} warehouses"
        );
    }

    /**
     * Bulk activate warehouses by ID.
     *
     * @param WarehouseBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(WarehouseBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateWarehouses($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} warehouses activated");
    }

    /**
     * Bulk deactivate warehouses by ID.
     *
     * @param WarehouseBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(WarehouseBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateWarehouses($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} warehouses deactivated");
    }

    /**
     * Import warehouses from Excel/CSV file.
     *
     * @param ImportRequest $request Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importWarehouses($request->file('file'));

        return response()->success(null, 'Warehouses imported successfully');
    }

    /**
     * Export warehouses to Excel or PDF.
     *
     * Supports download or email delivery based on method.
     *
     * @param ExportRequest $request Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportWarehouses(
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

    /**
     * Get all active warehouses.
     *
     * @return JsonResponse
     */
    public function getAllActive(): JsonResponse
    {
        $warehouses = $this->service->getAllActive();

        return response()->success(
            WarehouseResource::collection($warehouses),
            'Active warehouses retrieved successfully'
        );
    }
}
