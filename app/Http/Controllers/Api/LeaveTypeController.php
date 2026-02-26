<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\LeaveTypes\StoreLeaveTypeRequest;
use App\Http\Requests\LeaveTypes\UpdateLeaveTypeRequest;
use App\Http\Requests\LeaveTypes\LeaveTypeBulkActionRequest;
use App\Http\Resources\LeaveTypeResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\LeaveType;
use App\Services\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class LeaveTypeController
 *
 * API Controller for Leave Type CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to LeaveTypeService.
 *
 * @tags HRM Management
 */
class LeaveTypeController extends Controller
{
    /**
     * LeaveTypeController constructor.
     */
    public function __construct(
        private readonly LeaveTypeService $service
    ) {}

    /**
     * List Leave Types
     *
     * Display a paginated listing of leave types. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view leave types')) {
            return response()->forbidden('Permission denied for viewing leave types list.');
        }

        $leaveTypes = $this->service->getPaginatedLeaveTypes(
            $request->validate([
                /**
                 * Search term to filter leave types by name.
                 *
                 * @example "Annual"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter leave types starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter leave types up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            /**
             * Amount of items per page.
             *
             * @example 50
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            LeaveTypeResource::collection($leaveTypes),
            'Leave types retrieved successfully'
        );
    }

    /**
     * Get Leave Type Options
     *
     * Retrieve a simplified list of active leave types for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view leave types')) {
            return response()->forbidden('Permission denied for viewing leave types options.');
        }

        return response()->success($this->service->getOptions(), 'Leave type options retrieved successfully');
    }

    /**
     * Create Leave Type
     *
     * Store a newly created leave type in the system.
     */
    public function store(StoreLeaveTypeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create leave types')) {
            return response()->forbidden('Permission denied for create leave type.');
        }

        $leaveType = $this->service->createLeaveType($request->validated());

        return response()->success(
            new LeaveTypeResource($leaveType),
            'Leave type created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Leave Type
     *
     * Retrieve the details of a specific leave type by its ID.
     */
    public function show(LeaveType $leaveType): JsonResponse
    {
        if (auth()->user()->denies('view leave type details')) {
            return response()->forbidden('Permission denied for view leave type.');
        }

        return response()->success(
            new LeaveTypeResource($leaveType),
            'Leave type details retrieved successfully'
        );
    }

    /**
     * Update Leave Type
     *
     * Update the specified leave type's information.
     */
    public function update(UpdateLeaveTypeRequest $request, LeaveType $leaveType): JsonResponse
    {
        if (auth()->user()->denies('update leave types')) {
            return response()->forbidden('Permission denied for update leave type.');
        }

        $updatedLeaveType = $this->service->updateLeaveType($leaveType, $request->validated());

        return response()->success(
            new LeaveTypeResource($updatedLeaveType),
            'Leave type updated successfully'
        );
    }

    /**
     * Delete Leave Type
     *
     * Remove the specified leave type from storage.
     */
    public function destroy(LeaveType $leaveType): JsonResponse
    {
        if (auth()->user()->denies('delete leave types')) {
            return response()->forbidden('Permission denied for delete leave type.');
        }

        $this->service->deleteLeaveType($leaveType);

        return response()->success(null, 'Leave type deleted successfully');
    }

    /**
     * Bulk Delete Leave Types
     *
     * Delete multiple leave types simultaneously using an array of IDs.
     */
    public function bulkDestroy(LeaveTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete leave types')) {
            return response()->forbidden('Permission denied for bulk delete leave types.');
        }

        $count = $this->service->bulkDeleteLeaveTypes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} leave types"
        );
    }

    /**
     * Bulk Activate Leave Types
     *
     * Set the active status of multiple leave types to true.
     */
    public function bulkActivate(LeaveTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update leave types')) {
            return response()->forbidden('Permission denied for bulk update leave types.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} leave types activated"
        );
    }

    /**
     * Bulk Deactivate Leave Types
     *
     * Set the active status of multiple leave types to false.
     */
    public function bulkDeactivate(LeaveTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update leave types')) {
            return response()->forbidden('Permission denied for bulk update leave types.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} leave types deactivated"
        );
    }

    /**
     * Import Leave Types
     *
     * Import multiple leave types into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import leave types')) {
            return response()->forbidden('Permission denied for import leave types.');
        }

        $this->service->importLeaveTypes($request->file('file'));

        return response()->success(null, 'Leave types imported successfully');
    }

    /**
     * Export Leave Types
     *
     * Export a list of leave types to an Excel or PDF file.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export leave types')) {
            return response()->forbidden('Permission denied for export leave types.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

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
                    'leave_types_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Leave Types Export Is Ready',
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
     * Download Leave Type Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import leave types')) {
            return response()->forbidden('Permission denied for downloading leave types import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
