<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerGroups\CustomerGroupBulkActionRequest;
use App\Http\Requests\CustomerGroups\StoreCustomerGroupRequest;
use App\Http\Requests\CustomerGroups\UpdateCustomerGroupRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerGroupResource;
use App\Mail\ExportMail;
use App\Models\CustomerGroup;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CustomerGroupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CustomerGroupController
 *
 * API Controller for Customer Group CRUD and bulk operations.
 * Handles authorization via permissions and delegates logic to CustomerGroupService.
 *
 * @group Customer Group Management
 */
class CustomerGroupController extends Controller
{
    public function __construct(
        private readonly CustomerGroupService $service
    ) {}

    /**
     * Display a paginated listing of customer groups.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view customer groups')) {
            return response()->forbidden('Permission denied for viewing customer groups list.');
        }

        $customerGroups = $this->service->getPaginatedCustomerGroups(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            CustomerGroupResource::collection($customerGroups),
            'Customer groups retrieved successfully'
        );
    }

    /**
     * Get customer group options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view customer groups')) {
            return response()->forbidden('Permission denied for viewing customer groups options.');
        }

        return response()->success($this->service->getOptions(), 'Customer group options retrieved successfully');
    }

    /**
     * Store a newly created customer group.
     */
    public function store(StoreCustomerGroupRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create customer groups')) {
            return response()->forbidden('Permission denied for create customer group.');
        }

        $customerGroup = $this->service->createCustomerGroup($request->validated());

        return response()->success(
            new CustomerGroupResource($customerGroup),
            'Customer group created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified customer group.
     */
    public function show(CustomerGroup $customer_group): JsonResponse
    {
        if (auth()->user()->denies('view customer group details')) {
            return response()->forbidden('Permission denied for view customer group.');
        }

        return response()->success(
            new CustomerGroupResource($customer_group),
            'Customer group details retrieved successfully'
        );
    }

    /**
     * Update the specified customer group.
     */
    public function update(UpdateCustomerGroupRequest $request, CustomerGroup $customer_group): JsonResponse
    {
        if (auth()->user()->denies('update customer groups')) {
            return response()->forbidden('Permission denied for update customer group.');
        }

        $updated = $this->service->updateCustomerGroup($customer_group, $request->validated());

        return response()->success(
            new CustomerGroupResource($updated),
            'Customer group updated successfully'
        );
    }

    /**
     * Remove the specified customer group (soft delete).
     */
    public function destroy(CustomerGroup $customer_group): JsonResponse
    {
        if (auth()->user()->denies('delete customer groups')) {
            return response()->forbidden('Permission denied for delete customer group.');
        }

        $this->service->deleteCustomerGroup($customer_group);

        return response()->success(null, 'Customer group deleted successfully');
    }

    /**
     * Bulk delete customer groups.
     */
    public function bulkDestroy(CustomerGroupBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete customer groups')) {
            return response()->forbidden('Permission denied for bulk delete customer groups.');
        }

        $count = $this->service->bulkDeleteCustomerGroups($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} customer groups"
        );
    }

    /**
     * Bulk activate customer groups.
     */
    public function bulkActivate(CustomerGroupBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update customer groups')) {
            return response()->forbidden('Permission denied for bulk update customer groups.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} customer groups activated"
        );
    }

    /**
     * Bulk deactivate customer groups.
     */
    public function bulkDeactivate(CustomerGroupBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update customer groups')) {
            return response()->forbidden('Permission denied for bulk update customer groups.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} customer groups deactivated"
        );
    }

    /**
     * Import customer groups from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import customer groups')) {
            return response()->forbidden('Permission denied for import customer groups.');
        }

        $this->service->importCustomerGroups($request->file('file'));

        return response()->success(null, 'Customer groups imported successfully');
    }

    /**
     * Export customer groups to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export customer groups')) {
            return response()->forbidden('Permission denied for export customer groups.');
        }

        $validated = $request->validated();

        // 1. Generate the file via service
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        // 2. Handle Download Method
        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        // 3. Handle Email Method
        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'customer_groups_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Customer Groups Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download customer groups module import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import customer groups')) {
            return response()->forbidden('Permission denied for downloading customer groups import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
