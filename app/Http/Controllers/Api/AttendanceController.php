<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\AttendanceStatusEnum;
use App\Events\AttendancePunched;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendances\AttendanceBulkActionRequest;
use App\Http\Requests\Attendances\StoreAttendanceRequest;
use App\Http\Requests\Attendances\UpdateAttendanceRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\AttendanceResource;
use App\Mail\ExportMail;
use App\Models\Attendance;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\AttendanceService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class AttendanceController
 *
 * API Controller for Attendance CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to AttendanceService.
 *
 * @tags HRM Management
 */
class AttendanceController extends Controller
{
    /**
     * AttendanceController constructor.
     */
    public function __construct(
        private readonly AttendanceService $service
    ) {}

    /**
     * List Attendances
     *
     * Display a paginated listing of attendance records. Supports searching and filtering by status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view attendances')) {
            return response()->forbidden('Permission denied for viewing attendance list.');
        }

        $attendances = $this->service->getPaginatedAttendances(
            $request->validate([
                /**
                 * Search term to filter attendances by note or employee name.
                 *
                 * @example "Late"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by status (present, late, absent).
                 *
                 * @example "present"
                 */
                'status' => ['nullable', 'string'],
                /**
                 * Filter attendances starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter attendances up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                /**
                 * Filter by specific employee ID.
                 *
                 * @example 5
                 */
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
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
            AttendanceResource::collection($attendances),
            'Attendances retrieved successfully'
        );
    }

    /**
     * Create Attendance
     *
     * Store new attendance records. Supports bulk employee assignments and automatically calculates Present/Late statuses
     * against global HRM settings if no explicit status is provided.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create attendances')) {
            return response()->forbidden('Permission denied for creating attendance.');
        }

        $attendances = $this->service->createAttendance($request->validated());

        return response()->success(
            AttendanceResource::collection($attendances),
            'Attendance(s) created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Attendance
     *
     * Retrieve the details of a specific attendance record by its ID.
     */
    public function show(Attendance $attendance): JsonResponse
    {
        if (auth()->user()->denies('view attendances')) {
            return response()->forbidden('Permission denied for viewing attendance.');
        }

        return response()->success(
            new AttendanceResource($attendance->load(['employee', 'user'])),
            'Attendance details retrieved successfully'
        );
    }

    /**
     * Update Attendance
     *
     * Update the specified attendance record's information.
     */
    public function update(UpdateAttendanceRequest $request, Attendance $attendance): JsonResponse
    {
        if (auth()->user()->denies('update attendances')) {
            return response()->forbidden('Permission denied for updating attendance.');
        }

        $updatedAttendance = $this->service->updateAttendance($attendance, $request->validated());

        return response()->success(
            new AttendanceResource($updatedAttendance),
            'Attendance updated successfully'
        );
    }

    /**
     * Delete Attendance
     *
     * Remove the specified attendance record from storage.
     */
    public function destroy(Attendance $attendance): JsonResponse
    {
        if (auth()->user()->denies('delete attendances')) {
            return response()->forbidden('Permission denied for deleting attendance.');
        }

        $this->service->deleteAttendance($attendance);

        return response()->success(null, 'Attendance deleted successfully');
    }

    /**
     * Bulk Delete Attendances
     *
     * Delete multiple attendance records simultaneously using an array of IDs.
     */
    public function bulkDestroy(AttendanceBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete attendances')) {
            return response()->forbidden('Permission denied for bulk delete attendances.');
        }

        $count = $this->service->bulkDeleteAttendances($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} attendance records"
        );
    }

    /**
     * Bulk Mark Present
     *
     * Set the status of multiple attendance records to 'present'.
     */
    public function bulkMarkPresent(AttendanceBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update attendances')) {
            return response()->forbidden('Permission denied for bulk update attendances.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], AttendanceStatusEnum::PRESENT);

        return response()->success(
            ['updated_count' => $count],
            "{$count} attendance records marked as present"
        );
    }

    /**
     * Bulk Mark Late
     *
     * Set the status of multiple attendance records to 'late'.
     */
    public function bulkMarkLate(AttendanceBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update attendances')) {
            return response()->forbidden('Permission denied for bulk update attendances.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], AttendanceStatusEnum::LATE);

        return response()->success(
            ['updated_count' => $count],
            "{$count} attendance records marked as late"
        );
    }

    /**
     * Import Attendances
     *
     * Import multiple attendance records into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import attendances')) {
            return response()->forbidden('Permission denied for import attendances.');
        }

        $this->service->importAttendances($request->file('file'));

        return response()->success(null, 'Attendances imported successfully');
    }

    /**
     * Export Attendances
     *
     * Export a list of attendance records to an Excel or PDF file.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export attendances')) {
            return response()->forbidden('Permission denied for export attendances.');
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
                    'attendances_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Attendance Export Is Ready',
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
        if (auth()->user()->denies('import attendances')) {
            return response()->forbidden('Permission denied for downloading attendances import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Device Clock-In/Out Webhook (ADMS)
     *
     * Receives raw text payload from physical biometric/facial recognition machines.
     * Parses the ADMS format, logs the attendance, and triggers Reverb broadcasting.
     */
    public function deviceClock(Request $request): Response
    {
        // ZKTeco ADMS sends data in raw text body, not as JSON.
        $rawData = $request->getContent();

        // Split payload by line breaks (machines often batch-send multiple punches if network dropped)
        $lines = explode("\n", $rawData);

        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }

            // ADMS format: <User_PIN> <Date_Time> <Status> <Verify_Type>
            // Example: "EMP001\t2024-12-01 08:15:00\t1\t1"
            $parts = preg_split('/\s+/', trim($line));

            if (count($parts) >= 3) {
                $staffId = $parts[0];
                $timestamp = $parts[1].' '.$parts[2]; // Combine Date and Time

                try {
                    $result = $this->service->handleDevicePunch([
                        'staff_id' => $staffId,
                        'timestamp' => $timestamp,
                        'device_id' => $request->query('SN', 'Unknown-Device'), // Serial Number from query string
                    ]);

                    // If successfully recorded, fire Reverb WebSocket event!
                    if ($result) {
                        event(new AttendancePunched($result['attendance'], $result['type']));
                    }

                } catch (Exception $e) {
                    Log::error("Device ADMS sync failed for {$staffId}: ".$e->getMessage());
                }
            }
        }

        // ZKTeco STRICTLY requires the exact text "OK" to clear the log from its memory.
        return response('OK', 200)->header('Content-Type', 'text/plain');
    }

    /**
     * Web Clock-In/Out
     *
     * Allows authenticated employees to punch in/out directly from their web dashboard.
     * Uses strict server time to prevent manipulation.
     */
    public function webClock(Request $request): JsonResponse
    {
        if (auth()->user()->denies('web punch attendance')) {
            return response()->forbidden('Permission denied for web clock-in.');
        }

        try {
            // Calls the new web punch logic, passing the auth ID and IP Address
            $result = $this->service->handleWebPunch(auth()->id(), $request->ip());

            if (! $result) {
                return response()->error(
                    'Punch ignored. You either recently punched in, or attempted an early checkout before the official closing time.'
                );
            }

            // Optional: Broadcast event via Reverb so HR dashboard updates live!
            event(new AttendancePunched($result['attendance'], $result['type']));

            return response()->success(
                new AttendanceResource($result['attendance']),
                "Successfully recorded {$result['type']} via Web Portal."
            );

        } catch (Exception $e) {
            return response()->error($e->getMessage(), ResponseAlias::HTTP_BAD_REQUEST);
        }
    }
}
