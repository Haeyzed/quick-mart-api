<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Units\UnitBulkDestroyRequest;
use App\Http\Requests\Units\UnitBulkUpdateRequest;
use App\Http\Requests\Units\UnitIndexRequest;
use App\Http\Requests\Units\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;

/**
 * UnitController
 *
 * API controller for managing units with full CRUD operations.
 */
class UnitController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param UnitService $service
     */
    public function __construct(
        private readonly UnitService $service
    )
    {
    }

    /**
     * Display a paginated listing of units.
     *
     * @param UnitIndexRequest $request
     * @return JsonResponse
     */
    public function index(UnitIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $units = $this->service->getUnits($filters, $perPage)
            ->through(fn($unit) => new UnitResource($unit));

        return response()->success($units, 'Units fetched successfully');
    }

    /**
     * Store a newly created unit.
     *
     * @param UnitRequest $request
     * @return JsonResponse
     */
    public function store(UnitRequest $request): JsonResponse
    {
        $unit = $this->service->createUnit($request->validated());

        return response()->success(
            new UnitResource($unit),
            'Unit created successfully',
            201
        );
    }

    /**
     * Display the specified unit.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function show(Unit $unit): JsonResponse
    {
        return response()->success(
            new UnitResource($unit),
            'Unit retrieved successfully'
        );
    }

    /**
     * Get all active base units (for dropdown selection).
     *
     * @return JsonResponse
     */
    public function getBaseUnits(): JsonResponse
    {
        $baseUnits = $this->service->getBaseUnits()
            ->map(fn($unit) => new UnitResource($unit));

        return response()->success($baseUnits, 'Base units fetched successfully');
    }

    /**
     * Update the specified unit.
     *
     * @param UnitRequest $request
     * @param Unit $unit
     * @return JsonResponse
     */
    public function update(UnitRequest $request, Unit $unit): JsonResponse
    {
        $unit = $this->service->updateUnit($unit, $request->validated());

        return response()->success(
            new UnitResource($unit),
            'Unit updated successfully'
        );
    }

    /**
     * Remove the specified unit from storage.
     *
     * @param Unit $unit
     * @return JsonResponse
     */
    public function destroy(Unit $unit): JsonResponse
    {
        $this->service->deleteUnit($unit);

        return response()->success(null, 'Unit deleted successfully');
    }

    /**
     * Bulk delete multiple units.
     *
     * @param UnitBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(UnitBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteUnits($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} units successfully"
        );
    }

    /**
     * Import units from a file.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importUnits($request->file('file'));

        return response()->success(null, 'Units imported successfully');
    }

    /**
     * Bulk activate multiple units.
     *
     * @param UnitBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(UnitBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateUnits($request->validated()['ids']);

        return response()->success(
            ['activated_count' => $count],
            "Activated {$count} unit" . ($count !== 1 ? 's' : '') . " successfully"
        );
    }

    /**
     * Bulk deactivate multiple units.
     *
     * @param UnitBulkUpdateRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(UnitBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateUnits($request->validated()['ids']);

        return response()->success(
            ['deactivated_count' => $count],
            "Deactivated {$count} unit" . ($count !== 1 ? 's' : '') . " successfully"
        );
    }
}

