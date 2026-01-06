<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DesignationBulkDestroyRequest;
use App\Http\Requests\DesignationIndexRequest;
use App\Http\Requests\DesignationRequest;
use App\Http\Resources\DesignationResource;
use App\Models\Designation;
use App\Services\DesignationService;
use Illuminate\Http\JsonResponse;

/**
 * DesignationController
 *
 * API controller for managing designations with full CRUD operations.
 */
class DesignationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DesignationService $service
     */
    public function __construct(
        private readonly DesignationService $service
    )
    {
    }

    /**
     * Display a paginated listing of designations.
     *
     * @param DesignationIndexRequest $request
     * @return JsonResponse
     */
    public function index(DesignationIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $designations = $this->service->getDesignations($filters, $perPage)
            ->through(fn($designation) => new DesignationResource($designation));

        return response()->success($designations, 'Designations fetched successfully');
    }

    /**
     * Store a newly created designation.
     *
     * @param DesignationRequest $request
     * @return JsonResponse
     */
    public function store(DesignationRequest $request): JsonResponse
    {
        $designation = $this->service->createDesignation($request->validated());

        return response()->success(
            new DesignationResource($designation),
            'Designation created successfully',
            201
        );
    }

    /**
     * Display the specified designation.
     *
     * @param Designation $designation
     * @return JsonResponse
     */
    public function show(Designation $designation): JsonResponse
    {
        return response()->success(
            new DesignationResource($designation),
            'Designation retrieved successfully'
        );
    }

    /**
     * Update the specified designation.
     *
     * @param DesignationRequest $request
     * @param Designation $designation
     * @return JsonResponse
     */
    public function update(DesignationRequest $request, Designation $designation): JsonResponse
    {
        $designation = $this->service->updateDesignation($designation, $request->validated());

        return response()->success(
            new DesignationResource($designation),
            'Designation updated successfully'
        );
    }

    /**
     * Remove the specified designation from storage.
     *
     * @param Designation $designation
     * @return JsonResponse
     */
    public function destroy(Designation $designation): JsonResponse
    {
        $this->service->deleteDesignation($designation);

        return response()->success(null, 'Designation deleted successfully');
    }

    /**
     * Bulk delete multiple designations.
     *
     * @param DesignationBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DesignationBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteDesignations($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} designations successfully"
        );
    }
}
