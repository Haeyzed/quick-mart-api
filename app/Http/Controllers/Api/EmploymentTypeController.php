<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmploymentTypes\EmploymentTypeBulkActionRequest;
use App\Http\Requests\EmploymentTypes\StoreEmploymentTypeRequest;
use App\Http\Requests\EmploymentTypes\UpdateEmploymentTypeRequest;
use App\Http\Resources\EmploymentTypeResource;
use App\Models\EmploymentType;
use App\Services\EmploymentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class EmploymentTypeController
 *
 * API Controller for Employment Type CRUD and bulk operations.
 *
 * @tags HRM Management
 */
class EmploymentTypeController extends Controller
{
    public function __construct(
        private readonly EmploymentTypeService $service
    ) {}

    /**
     * List Employment Types
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employment types')) {
            return response()->forbidden('Permission denied for viewing employment types.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'search' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            EmploymentTypeResource::collection($items),
            'Employment types retrieved successfully'
        );
    }

    /**
     * Get Employment Type Options
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view employment types')) {
            return response()->forbidden('Permission denied for viewing employment type options.');
        }

        return response()->success($this->service->getOptions(), 'Employment type options retrieved successfully');
    }

    /**
     * Create Employment Type
     */
    public function store(StoreEmploymentTypeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create employment types')) {
            return response()->forbidden('Permission denied for creating employment type.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new EmploymentTypeResource($model),
            'Employment type created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Employment Type
     */
    public function show(EmploymentType $employment_type): JsonResponse
    {
        if (auth()->user()->denies('view employment types')) {
            return response()->forbidden('Permission denied for viewing employment type.');
        }

        return response()->success(
            new EmploymentTypeResource($employment_type),
            'Employment type details retrieved successfully'
        );
    }

    /**
     * Update Employment Type
     */
    public function update(UpdateEmploymentTypeRequest $request, EmploymentType $employment_type): JsonResponse
    {
        if (auth()->user()->denies('update employment types')) {
            return response()->forbidden('Permission denied for updating employment type.');
        }

        $updated = $this->service->update($employment_type, $request->validated());

        return response()->success(
            new EmploymentTypeResource($updated),
            'Employment type updated successfully'
        );
    }

    /**
     * Delete Employment Type
     */
    public function destroy(EmploymentType $employment_type): JsonResponse
    {
        if (auth()->user()->denies('delete employment types')) {
            return response()->forbidden('Permission denied for deleting employment type.');
        }

        $this->service->delete($employment_type);

        return response()->success(null, 'Employment type deleted successfully');
    }

    /**
     * Bulk Delete Employment Types
     */
    public function bulkDestroy(EmploymentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete employment types')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} employment types"
        );
    }

    /**
     * Bulk Activate Employment Types
     */
    public function bulkActivate(EmploymentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employment types')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} employment types activated"
        );
    }

    /**
     * Bulk Deactivate Employment Types
     */
    public function bulkDeactivate(EmploymentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employment types')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} employment types deactivated"
        );
    }
}
