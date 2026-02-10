<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerGroupBulkDestroyRequest;
use App\Http\Requests\CustomerGroupIndexRequest;
use App\Http\Requests\CustomerGroupRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Services\CustomerGroupService;
use Illuminate\Http\JsonResponse;

/**
 * CustomerGroupController
 *
 * Handles HTTP requests for customer group management operations.
 * Provides RESTful API endpoints for CRUD operations and bulk deletion.
 */
class CustomerGroupController extends Controller
{
    public function __construct(
        private readonly CustomerGroupService $customerGroupService
    ) {}

    /**
     * Display a paginated listing of customer groups.
     *
     * @param  CustomerGroupIndexRequest  $request  Validated query params: per_page, page, is_active, search.
     * @return JsonResponse Paginated customer groups with meta and links.
     */
    public function index(CustomerGroupIndexRequest $request): JsonResponse
    {
        $customerGroups = $this->customerGroupService->getCustomerGroups(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $customerGroups->through(fn (CustomerGroup $group) => new CustomerGroupResource($group));

        return response()->success(
            $customerGroups,
            'Customer groups retrieved successfully'
        );
    }

    /**
     * Store a newly created customer group.
     */
    public function store(CustomerGroupRequest $request): JsonResponse
    {
        $customerGroup = $this->customerGroupService->createCustomerGroup($request->validated());

        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group created successfully',
            201
        );
    }

    /**
     * Display the specified customer group.
     */
    public function show(CustomerGroup $customerGroup): JsonResponse
    {
        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group retrieved successfully'
        );
    }

    /**
     * Update the specified customer group.
     */
    public function update(CustomerGroupRequest $request, CustomerGroup $customerGroup): JsonResponse
    {
        $customerGroup = $this->customerGroupService->updateCustomerGroup($customerGroup, $request->validated());

        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group updated successfully'
        );
    }

    /**
     * Remove the specified customer group.
     */
    public function destroy(CustomerGroup $customerGroup): JsonResponse
    {
        $this->customerGroupService->deleteCustomerGroup($customerGroup);

        return response()->success(
            null,
            'Customer group deleted successfully'
        );
    }

    /**
     * Bulk delete multiple customer groups.
     */
    public function bulkDestroy(CustomerGroupBulkDestroyRequest $request): JsonResponse
    {
        $ids = $request->validated()['ids'];
        $deletedCount = $this->customerGroupService->bulkDeleteCustomerGroups($ids);

        return response()->success(
            ['deleted_count' => $deletedCount],
            "Successfully deleted {$deletedCount} customer group(s)"
        );
    }

    /**
     * Import customer groups from a file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->customerGroupService->importCustomerGroups($request->file('file'));

        return response()->success(null, 'Customer groups imported successfully');
    }

    /**
     * Get all active customer groups.
     */
    public function getAllActive(): JsonResponse
    {
        $customerGroups = $this->customerGroupService->getAllActive();

        return response()->success(
            CustomerGroupResource::collection($customerGroups),
            'Active customer groups retrieved successfully'
        );
    }
}
