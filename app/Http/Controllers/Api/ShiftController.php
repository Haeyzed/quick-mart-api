<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Shifts\StoreShiftRequest;
use App\Http\Requests\Shifts\UpdateShiftRequest;
use App\Http\Requests\Shifts\ShiftBulkActionRequest;
use App\Http\Resources\ShiftResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class ShiftController
 *
 * API Controller for Shift CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to ShiftService.
 *
 * @tags HRM Management
 */
class ShiftController extends Controller
{
    /**
     * ShiftController constructor.
     */
    public function __construct(
        private readonly ShiftService $service
    ) {}

    /**
     * List Shifts
     *
     * Display a paginated listing of shifts. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view shifts')) {
            return response()->forbidden('Permission denied for viewing shifts list.');
        }

        $shifts = $this->service->getPaginatedShifts(
            $request->validate([
                /**
                 * Search term to filter shifts by name.
                 *
                 * @example "Morning"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter shifts starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter shifts up to this date.
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
            ShiftResource::collection($shifts),
            'Shifts retrieved successfully'
        );
    }

    /**
     * Get Shift Options
     *
     * Retrieve a simplified list of active shifts for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view shifts')) {
            return response()->forbidden('Permission denied for viewing shift options.');
        }

        return response()->success($this->service->getOptions(), 'Shift options retrieved successfully');
    }

    /**
     * Create Shift
     *
     * Store a newly created shift in the system.
     */
    public function store(StoreShiftRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create shifts')) {
            return response()->forbidden('Permission denied for create shift.');
        }

        $shift = $this->service->createShift($request->validated());

        return response()->success(
            new ShiftResource($shift),
            'Shift created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Shift
     *
     * Retrieve the details of a specific shift by its ID.
     */
    public function show(Shift $shift): JsonResponse
    {
        if (auth()->user()->denies('view shift details')) {
            return response()->forbidden('Permission denied for view shift.');
        }

        return response()->success(
            new ShiftResource($shift),
            'Shift details retrieved successfully'
        );
    }

    /**
     * Update Shift
     *
     * Update the specified shift's information.
     */
    public function update(UpdateShiftRequest $request, Shift $shift): JsonResponse
    {
        if (auth()->user()->denies('update shifts')) {
            return response()->forbidden('Permission denied for update shift.');
        }

        $updatedShift = $this->service->updateShift($shift, $request->validated());

        return response()->success(
            new ShiftResource($updatedShift),
            'Shift updated successfully'
        );
    }

    /**
     * Delete Shift
     *
     * Remove the specified shift from storage.
     */
    public function destroy(Shift $shift): JsonResponse
    {
        if (auth()->user()->denies('delete shifts')) {
            return response()->forbidden('Permission denied for delete shift.');
        }

        $this->service->deleteShift($shift);

        return response()->success(null, 'Shift deleted successfully');
    }

    /**
     * Bulk Delete Shifts
     *
     * Delete multiple shifts simultaneously using an array of IDs.
     */
    public function bulkDestroy(ShiftBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete shifts')) {
            return response()->forbidden('Permission denied for bulk delete shifts.');
        }

        $count = $this->service->bulkDeleteShifts($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} shifts"
        );
    }

    /**
     * Bulk Activate Shifts
     *
     * Set the active status of multiple shifts to true.
     */
    public function bulkActivate(ShiftBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update shifts')) {
            return response()->forbidden('Permission denied for bulk update shifts.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} shifts activated"
        );
    }

    /**
     * Bulk Deactivate Shifts
     *
     * Set the active status of multiple shifts to false.
     */
    public function bulkDeactivate(ShiftBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update shifts')) {
            return response()->forbidden('Permission denied for bulk update shifts.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} shifts deactivated"
        );
    }

    /**
     * Import Shifts
     *
     * Import multiple shifts into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import shifts')) {
            return response()->forbidden('Permission denied for import shifts.');
        }

        $this->service->importShifts($request->file('file'));

        return response()->success(null, 'Shifts imported successfully');
    }

    /**
     * Export Shifts
     *
     * Export a list of shifts to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export shifts')) {
            return response()->forbidden('Permission denied for export shifts.');
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
                    'shifts_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Shift Export Is Ready',
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
     * Download Shift Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import shifts')) {
            return response()->forbidden('Permission denied for downloading shifts import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
