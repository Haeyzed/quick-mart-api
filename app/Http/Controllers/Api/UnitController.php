<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Units\UnitBulkDestroyRequest;
use App\Http\Requests\Units\UnitBulkUpdateRequest;
use App\Http\Requests\Units\UnitIndexRequest;
use App\Http\Requests\Units\UnitRequest;
use App\Http\Resources\UnitResource;
use App\Models\Unit;
use App\Models\User;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Unit CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, and export. All responses use the ResponseServiceProvider macros.
 *
 * @group Unit Management
 */
class UnitController extends Controller
{
    /**
     * UnitController constructor.
     *
     * @param UnitService $service
     */
    public function __construct(
        private readonly UnitService $service
    ) {}

    /**
     * Display a paginated listing of units.
     *
     * @param UnitIndexRequest $request Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated units with meta and links.
     */
    public function index(UnitIndexRequest $request): JsonResponse
    {
        $units = $this->service->getUnits(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $units->through(fn (Unit $unit) => new UnitResource($unit));

        return response()->success(
            $units,
            'Units fetched successfully'
        );
    }

    /**
     * Store a newly created unit.
     *
     * @param UnitRequest $request Validated unit attributes.
     * @return JsonResponse Created unit with 201 status.
     */
    public function store(UnitRequest $request): JsonResponse
    {
        $unit = $this->service->createUnit($request->validated());

        return response()->success(
            new UnitResource($unit),
            'Unit created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified unit.
     *
     * Requires units-index permission. Returns the unit as a resource.
     *
     * @param Unit $unit The unit instance resolved via route model binding.
     * @return JsonResponse
     */
    public function show(Unit $unit): JsonResponse
    {
        $unit = $this->service->getUnit($unit);

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
            ->map(fn (Unit $unit) => [
                'value' => $unit->id,
                'label' => $unit->name . ' (' . $unit->code . ')',
                'code' => $unit->code,
            ]);

        return response()->success($baseUnits, 'Base units fetched successfully');
    }

    /**
     * Update the specified unit.
     *
     * @param UnitRequest $request Validated unit attributes.
     * @param Unit $unit The unit instance to update.
     * @return JsonResponse Updated unit.
     */
    public function update(UnitRequest $request, Unit $unit): JsonResponse
    {
        $updatedUnit = $this->service->updateUnit($unit, $request->validated());

        return response()->success(
            new UnitResource($updatedUnit),
            'Unit updated successfully'
        );
    }

    /**
     * Remove the specified unit (soft delete).
     *
     * @param Unit $unit The unit instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        $this->service->deleteUnit($unit);

        return response()->success(null, 'Unit deleted successfully');
    }

    /**
     * Bulk delete units (soft delete). Skips units with products or sub-units.
     *
     * @param UnitBulkDestroyRequest $request Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(UnitBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteUnits($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} units"
        );
    }

    /**
     * Bulk activate units by ID.
     *
     * @param UnitBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(UnitBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateUnits($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} units activated");
    }

    /**
     * Bulk deactivate units by ID.
     *
     * @param UnitBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(UnitBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateUnits($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} units deactivated");
    }

    /**
     * Import units from Excel/CSV file.
     *
     * @param ImportRequest $request Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importUnits($request->file('file'));

        return response()->success(null, 'Units imported successfully');
    }

    /**
     * Export units to Excel or PDF.
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

        $filePath = $this->service->exportUnits(
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

