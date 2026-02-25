<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Overtimes\StoreOvertimeRequest;
use App\Http\Requests\Overtimes\UpdateOvertimeRequest;
use App\Http\Requests\Overtimes\OvertimeBulkActionRequest;
use App\Http\Resources\OvertimeResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Overtime;
use App\Services\OvertimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class OvertimeController
 *
 * API Controller for Overtime CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to OvertimeService.
 *
 * @tags HRM Management
 */
class OvertimeController extends Controller
{
    /**
     * OvertimeController constructor.
     */
    public function __construct(
        private readonly OvertimeService $service
    ) {}

    /**
     * List Overtimes
     *
     * Display a paginated listing of overtime requests. Supports searching and filtering by status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view overtimes')) {
            return response()->forbidden('Permission denied for viewing overtimes list.');
        }

        $overtimes = $this->service->getPaginatedOvertimes(
            $request->validate([
                /**
                 * Search term to filter overtimes by employee name.
                 *
                 * @example "John Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by overtime status (Pending, Approved, Rejected).
                 *
                 * @example "Pending"
                 */
                'status' => ['nullable', 'in:pending,approved,rejected'],
                /**
                 * Filter overtimes starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter overtimes up to this date.
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
            OvertimeResource::collection($overtimes),
            'Overtimes retrieved successfully'
        );
    }

    /**
     * Create Overtime
     *
     * Store a newly created overtime request in the system.
     */
    public function store(StoreOvertimeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create overtimes')) {
            return response()->forbidden('Permission denied for create overtime.');
        }

        $overtime = $this->service->createOvertime($request->validated());

        return response()->success(
            new OvertimeResource($overtime),
            'Overtime created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Overtime
     *
     * Retrieve the details of a specific overtime request by its ID.
     */
    public function show(Overtime $overtime): JsonResponse
    {
        if (auth()->user()->denies('view overtime details')) {
            return response()->forbidden('Permission denied for view overtime.');
        }

        return response()->success(
            new OvertimeResource($overtime->load(['employee', 'approver'])),
            'Overtime details retrieved successfully'
        );
    }

    /**
     * Update Overtime
     *
     * Update the specified overtime request's information or status.
     */
    public function update(UpdateOvertimeRequest $request, Overtime $overtime): JsonResponse
    {
        if (auth()->user()->denies('update overtimes')) {
            return response()->forbidden('Permission denied for update overtime.');
        }

        $updatedOvertime = $this->service->updateOvertime($overtime, $request->validated());

        return response()->success(
            new OvertimeResource($updatedOvertime),
            'Overtime updated successfully'
        );
    }

    /**
     * Delete Overtime
     *
     * Remove the specified overtime request from storage.
     */
    public function destroy(Overtime $overtime): JsonResponse
    {
        if (auth()->user()->denies('delete overtimes')) {
            return response()->forbidden('Permission denied for delete overtime.');
        }

        $this->service->deleteOvertime($overtime);

        return response()->success(null, 'Overtime deleted successfully');
    }

    /**
     * Bulk Delete Overtimes
     *
     * Delete multiple overtime requests simultaneously using an array of IDs.
     */
    public function bulkDestroy(OvertimeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete overtimes')) {
            return response()->forbidden('Permission denied for bulk delete overtimes.');
        }

        $count = $this->service->bulkDeleteOvertimes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} overtimes"
        );
    }

    /**
     * Bulk Approve Overtimes
     *
     * Set the status of multiple overtime requests to Approved.
     */
    public function bulkApprove(OvertimeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update overtimes')) {
            return response()->forbidden('Permission denied for bulk approve overtimes.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], 'Approved');

        return response()->success(
            ['approved_count' => $count],
            "{$count} overtimes approved"
        );
    }

    /**
     * Bulk Reject Overtimes
     *
     * Set the status of multiple overtime requests to Rejected.
     */
    public function bulkReject(OvertimeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update overtimes')) {
            return response()->forbidden('Permission denied for bulk reject overtimes.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], 'Rejected');

        return response()->success(
            ['rejected_count' => $count],
            "{$count} overtimes rejected"
        );
    }

    /**
     * Import Overtimes
     *
     * Import multiple overtime requests into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import overtimes')) {
            return response()->forbidden('Permission denied for import overtimes.');
        }

        $this->service->importOvertimes($request->file('file'));

        return response()->success(null, 'Overtimes imported successfully');
    }

    /**
     * Export Overtimes
     *
     * Export a list of overtime requests to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export overtimes')) {
            return response()->forbidden('Permission denied for export overtimes.');
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
                    'overtimes_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Overtimes Export Is Ready',
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
        if (auth()->user()->denies('import overtimes')) {
            return response()->forbidden('Permission denied for downloading overtimes import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
