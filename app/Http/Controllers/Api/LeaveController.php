<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Leaves\StoreLeaveRequest;
use App\Http\Requests\Leaves\UpdateLeaveRequest;
use App\Http\Requests\Leaves\LeaveBulkActionRequest;
use App\Http\Resources\LeaveResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Leave;
use App\Services\LeaveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class LeaveController
 *
 * API Controller for Leave CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to LeaveService.
 *
 * @tags Leave Management
 */
class LeaveController extends Controller
{
    /**
     * LeaveController constructor.
     */
    public function __construct(
        private readonly LeaveService $service
    ) {}

    /**
     * List Leaves
     *
     * Display a paginated listing of leave requests. Supports searching and filtering by status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view leaves')) {
            return response()->forbidden('Permission denied for viewing leaves list.');
        }

        $leaves = $this->service->getPaginatedLeaves(
            $request->validate([
                /**
                 * Search term to filter leaves by employee name.
                 *
                 * @example "John Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by leave status (Pending, Approved, Rejected).
                 *
                 * @example "Pending"
                 */
                'status' => ['nullable', 'in:Pending,Approved,Rejected'],
                /**
                 * Filter leaves starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter leaves up to this date.
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
            LeaveResource::collection($leaves),
            'Leaves retrieved successfully'
        );
    }

    /**
     * Create Leave
     *
     * Store a newly created leave request in the system.
     */
    public function store(StoreLeaveRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create leaves')) {
            return response()->forbidden('Permission denied for create leave.');
        }

        $leave = $this->service->createLeave($request->validated());

        return response()->success(
            new LeaveResource($leave),
            'Leave created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Leave
     *
     * Retrieve the details of a specific leave request by its ID.
     */
    public function show(Leave $leave): JsonResponse
    {
        if (auth()->user()->denies('view leave details')) {
            return response()->forbidden('Permission denied for view leave.');
        }

        return response()->success(
            new LeaveResource($leave->load(['employee', 'leaveType'])),
            'Leave details retrieved successfully'
        );
    }

    /**
     * Update Leave
     *
     * Update the specified leave request's information or status.
     */
    public function update(UpdateLeaveRequest $request, Leave $leave): JsonResponse
    {
        if (auth()->user()->denies('update leaves')) {
            return response()->forbidden('Permission denied for update leave.');
        }

        $updatedLeave = $this->service->updateLeave($leave, $request->validated());

        return response()->success(
            new LeaveResource($updatedLeave),
            'Leave updated successfully'
        );
    }

    /**
     * Delete Leave
     *
     * Remove the specified leave request from storage.
     */
    public function destroy(Leave $leave): JsonResponse
    {
        if (auth()->user()->denies('delete leaves')) {
            return response()->forbidden('Permission denied for delete leave.');
        }

        $this->service->deleteLeave($leave);

        return response()->success(null, 'Leave deleted successfully');
    }

    /**
     * Bulk Delete Leaves
     *
     * Delete multiple leave requests simultaneously using an array of IDs.
     */
    public function bulkDestroy(LeaveBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete leaves')) {
            return response()->forbidden('Permission denied for bulk delete leaves.');
        }

        $count = $this->service->bulkDeleteLeaves($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} leaves"
        );
    }

    /**
     * Bulk Approve Leaves
     *
     * Set the status of multiple leave requests to Approved.
     */
    public function bulkApprove(LeaveBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update leaves')) {
            return response()->forbidden('Permission denied for bulk approve leaves.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], 'Approved');

        return response()->success(
            ['approved_count' => $count],
            "{$count} leaves approved"
        );
    }

    /**
     * Bulk Reject Leaves
     *
     * Set the status of multiple leave requests to Rejected.
     */
    public function bulkReject(LeaveBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update leaves')) {
            return response()->forbidden('Permission denied for bulk reject leaves.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], 'Rejected');

        return response()->success(
            ['rejected_count' => $count],
            "{$count} leaves rejected"
        );
    }

    /**
     * Import Leaves
     *
     * Import multiple leave requests into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import leaves')) {
            return response()->forbidden('Permission denied for import leaves.');
        }

        $this->service->importLeaves($request->file('file'));

        return response()->success(null, 'Leaves imported successfully');
    }

    /**
     * Export Leaves
     *
     * Export a list of leave requests to an Excel or PDF file.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export leaves')) {
            return response()->forbidden('Permission denied for export leaves.');
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
                    'leaves_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Leaves Export Is Ready',
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
     * Download Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import leaves')) {
            return response()->forbidden('Permission denied for downloading leaves import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
