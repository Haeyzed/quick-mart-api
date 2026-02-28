<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WorkLocations\StoreWorkLocationRequest;
use App\Http\Requests\WorkLocations\UpdateWorkLocationRequest;
use App\Http\Requests\WorkLocations\WorkLocationBulkActionRequest;
use App\Http\Resources\WorkLocationResource;
use App\Models\WorkLocation;
use App\Services\WorkLocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class WorkLocationController
 *
 * API Controller for Work Location CRUD and bulk operations.
 *
 * @tags HRM Management
 */
class WorkLocationController extends Controller
{
    public function __construct(
        private readonly WorkLocationService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view work locations')) {
            return response()->forbidden('Permission denied for viewing work locations.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'search' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            WorkLocationResource::collection($items),
            'Work locations retrieved successfully'
        );
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view work locations')) {
            return response()->forbidden('Permission denied for viewing work location options.');
        }

        return response()->success($this->service->getOptions(), 'Work location options retrieved successfully');
    }

    public function store(StoreWorkLocationRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create work locations')) {
            return response()->forbidden('Permission denied for creating work location.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new WorkLocationResource($model),
            'Work location created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    public function show(WorkLocation $work_location): JsonResponse
    {
        if (auth()->user()->denies('view work locations')) {
            return response()->forbidden('Permission denied for viewing work location.');
        }

        return response()->success(
            new WorkLocationResource($work_location),
            'Work location details retrieved successfully'
        );
    }

    public function update(UpdateWorkLocationRequest $request, WorkLocation $work_location): JsonResponse
    {
        if (auth()->user()->denies('update work locations')) {
            return response()->forbidden('Permission denied for updating work location.');
        }

        $updated = $this->service->update($work_location, $request->validated());

        return response()->success(
            new WorkLocationResource($updated),
            'Work location updated successfully'
        );
    }

    public function destroy(WorkLocation $work_location): JsonResponse
    {
        if (auth()->user()->denies('delete work locations')) {
            return response()->forbidden('Permission denied for deleting work location.');
        }

        $this->service->delete($work_location);

        return response()->success(null, 'Work location deleted successfully');
    }

    public function bulkDestroy(WorkLocationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete work locations')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} work locations"
        );
    }

    public function bulkActivate(WorkLocationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update work locations')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} work locations activated"
        );
    }

    public function bulkDeactivate(WorkLocationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update work locations')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} work locations deactivated"
        );
    }
}
