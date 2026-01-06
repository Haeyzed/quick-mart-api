<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepartmentBulkDestroyRequest;
use App\Http\Requests\DepartmentIndexRequest;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;

/**
 * DepartmentController
 *
 * API controller for managing departments with full CRUD operations.
 */
class DepartmentController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param DepartmentService $service
     */
    public function __construct(
        private readonly DepartmentService $service
    )
    {
    }

    /**
     * Display a paginated listing of departments.
     *
     * @param DepartmentIndexRequest $request
     * @return JsonResponse
     */
    public function index(DepartmentIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $departments = $this->service->getDepartments($filters, $perPage)
            ->through(fn($department) => new DepartmentResource($department));

        return response()->success($departments, 'Departments fetched successfully');
    }

    /**
     * Store a newly created department.
     *
     * @param DepartmentRequest $request
     * @return JsonResponse
     */
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department = $this->service->createDepartment($request->validated());

        return response()->success(
            new DepartmentResource($department),
            'Department created successfully',
            201
        );
    }

    /**
     * Display the specified department.
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function show(Department $department): JsonResponse
    {
        return response()->success(
            new DepartmentResource($department),
            'Department retrieved successfully'
        );
    }

    /**
     * Update the specified department.
     *
     * @param DepartmentRequest $request
     * @param Department $department
     * @return JsonResponse
     */
    public function update(DepartmentRequest $request, Department $department): JsonResponse
    {
        $department = $this->service->updateDepartment($department, $request->validated());

        return response()->success(
            new DepartmentResource($department),
            'Department updated successfully'
        );
    }

    /**
     * Remove the specified department from storage.
     *
     * @param Department $department
     * @return JsonResponse
     */
    public function destroy(Department $department): JsonResponse
    {
        $this->service->deleteDepartment($department);

        return response()->success(null, 'Department deleted successfully');
    }

    /**
     * Bulk delete multiple departments.
     *
     * @param DepartmentBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(DepartmentBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteDepartments($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} departments successfully"
        );
    }
}
