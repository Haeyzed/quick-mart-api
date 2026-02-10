<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerGroups\CustomerGroupBulkDestroyRequest;
use App\Http\Requests\CustomerGroups\CustomerGroupBulkUpdateRequest;
use App\Http\Requests\CustomerGroups\CustomerGroupIndexRequest;
use App\Http\Requests\CustomerGroups\CustomerGroupRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Models\CustomerGroup;
use App\Models\User;
use App\Services\CustomerGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Customer Group CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, and getAllActive. All responses use the ResponseServiceProvider macros.
 *
 * @group Customer Group Management
 */
class CustomerGroupController extends Controller
{
    /**
     * CustomerGroupController constructor.
     */
    public function __construct(
        private readonly CustomerGroupService $service
    ) {}

    /**
     * Display a paginated listing of customer groups.
     *
     * @param  CustomerGroupIndexRequest  $request  Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated customer groups with meta and links.
     */
    public function index(CustomerGroupIndexRequest $request): JsonResponse
    {
        $customerGroups = $this->service->getCustomerGroups(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $customerGroups->through(fn (CustomerGroup $customerGroup) => new CustomerGroupResource($customerGroup));

        return response()->success(
            $customerGroups,
            'Customer groups fetched successfully'
        );
    }

    /**
     * Store a newly created customer group.
     *
     * @param  CustomerGroupRequest  $request  Validated customer group attributes.
     * @return JsonResponse Created customer group with 201 status.
     */
    public function store(CustomerGroupRequest $request): JsonResponse
    {
        $customerGroup = $this->service->createCustomerGroup($request->validated());

        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified customer group.
     *
     * @param  CustomerGroup  $customerGroup  The customer group instance resolved via route model binding.
     */
    public function show(CustomerGroup $customerGroup): JsonResponse
    {
        $customerGroup = $this->service->getCustomerGroup($customerGroup);

        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group retrieved successfully'
        );
    }

    /**
     * Update the specified customer group.
     *
     * @param  CustomerGroupRequest  $request  Validated customer group attributes.
     * @param  CustomerGroup  $customerGroup  The customer group instance to update.
     * @return JsonResponse Updated customer group.
     */
    public function update(CustomerGroupRequest $request, CustomerGroup $customerGroup): JsonResponse
    {
        $updatedCustomerGroup = $this->service->updateCustomerGroup($customerGroup, $request->validated());

        return response()->success(
            new CustomerGroupResource($updatedCustomerGroup),
            'Customer group updated successfully'
        );
    }

    /**
     * Remove the specified customer group.
     *
     * @param  CustomerGroup  $customerGroup  The customer group instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(CustomerGroup $customerGroup): JsonResponse
    {
        $this->service->deleteCustomerGroup($customerGroup);

        return response()->success(null, 'Customer group deleted successfully');
    }

    /**
     * Bulk delete customer groups.
     *
     * @param  CustomerGroupBulkDestroyRequest  $request  Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(CustomerGroupBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCustomerGroups($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} customer group(s)"
        );
    }

    /**
     * Bulk activate customer groups by ID.
     *
     * @param  CustomerGroupBulkUpdateRequest  $request  Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(CustomerGroupBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateCustomerGroups($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} customer group(s) activated");
    }

    /**
     * Bulk deactivate customer groups by ID.
     *
     * @param  CustomerGroupBulkUpdateRequest  $request  Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(CustomerGroupBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateCustomerGroups($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} customer group(s) deactivated");
    }

    /**
     * Import customer groups from Excel/CSV file.
     *
     * @param  ImportRequest  $request  Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importCustomerGroups($request->file('file'));

        return response()->success(null, 'Customer groups imported successfully');
    }

    /**
     * Export customer groups to Excel or PDF.
     *
     * @param  ExportRequest  $request  Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportCustomerGroups(
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
     * Get all active customer groups.
     */
    public function getAllActive(): JsonResponse
    {
        $customerGroups = $this->service->getAllActive();

        return response()->success(
            CustomerGroupResource::collection($customerGroups),
            'Active customer groups retrieved successfully'
        );
    }
}
