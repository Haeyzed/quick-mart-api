<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\Holidays\HolidayBulkActionRequest;
use App\Http\Requests\Holidays\StoreHolidayRequest;
use App\Http\Requests\Holidays\UpdateHolidayRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\HolidayResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\Holiday;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class HolidayController
 *
 * API Controller for Holiday CRUD, bulk operations, import/export and approval.
 * Handles authorization via Policy and delegates logic to HolidayService.
 *
 * @tags Holiday Management
 */
class HolidayController extends Controller
{
    /**
     * HolidayController constructor.
     *
     * @param  HolidayService  $service  Service handling holiday business logic.
     */
    public function __construct(
        private readonly HolidayService $service
    ) {}

    /**
     * List Holidays
     *
     * Display a paginated listing of holidays. Supports searching, filtering by user, approval status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (Auth::user()->denies('view holidays')) {
            return response()->forbidden('Permission denied for viewing holidays list.');
        }

        $holidays = $this->service->getPaginatedHolidays(
            $request->validate([
                /**
                 * Search term to filter holidays by note.
                 *
                 * @example "Annual leave"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by user ID.
                 *
                 * @example 1
                 */
                'user_id' => ['nullable', 'integer', 'exists:users,id'],
                /**
                 * Filter by approval status.
                 *
                 * @example true
                 */
                'is_approved' => ['nullable', 'boolean'],
                /**
                 * Filter holidays starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter holidays up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            HolidayResource::collection($holidays),
            'Holidays retrieved successfully'
        );
    }

    /**
     * Create Holiday
     *
     * Store a newly created holiday (leave request) in the system.
     */
    public function store(StoreHolidayRequest $request): JsonResponse
    {
        if (Auth::user()->denies('create holidays')) {
            return response()->forbidden('Permission denied for create holiday.');
        }

        $holiday = $this->service->createHoliday($request->validated());

        return response()->success(
            new HolidayResource($holiday),
            'Holiday created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Holiday
     *
     * Retrieve the details of a specific holiday by its ID.
     */
    public function show(Holiday $holiday): JsonResponse
    {
        if (Auth::user()->denies('view holiday details')) {
            return response()->forbidden('Permission denied for view holiday.');
        }

        return response()->success(
            new HolidayResource($holiday->load('user')),
            'Holiday details retrieved successfully'
        );
    }

    /**
     * Update Holiday
     *
     * Update the specified holiday's information.
     */
    public function update(UpdateHolidayRequest $request, Holiday $holiday): JsonResponse
    {
        if (Auth::user()->denies('update holidays')) {
            return response()->forbidden('Permission denied for update holiday.');
        }

        $updated = $this->service->updateHoliday($holiday, $request->validated());

        return response()->success(
            new HolidayResource($updated),
            'Holiday updated successfully'
        );
    }

    /**
     * Delete Holiday
     *
     * Remove the specified holiday from storage.
     */
    public function destroy(Holiday $holiday): JsonResponse
    {
        if (Auth::user()->denies('delete holidays')) {
            return response()->forbidden('Permission denied for delete holiday.');
        }

        $this->service->deleteHoliday($holiday);

        return response()->success(null, 'Holiday deleted successfully');
    }

    /**
     * Bulk Delete Holidays
     *
     * Delete multiple holidays simultaneously using an array of IDs.
     */
    public function bulkDestroy(HolidayBulkActionRequest $request): JsonResponse
    {
        if (Auth::user()->denies('delete holidays')) {
            return response()->forbidden('Permission denied for bulk delete holidays.');
        }

        $count = $this->service->bulkDeleteHolidays($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} holidays"
        );
    }

    /**
     * Approve Holiday
     *
     * Approve a holiday request and send approval email to the user.
     */
    public function approve(Holiday $holiday): JsonResponse
    {
        if (Auth::user()->denies('approve holidays')) {
            return response()->forbidden('Permission denied for approve holiday.');
        }

        $holiday = $this->service->approveHoliday($holiday);

        return response()->success(
            new HolidayResource($holiday),
            'Holiday approved successfully'
        );
    }

    /**
     * Get User Holidays by Month
     *
     * Retrieve the authenticated user's holidays for a specific month (calendar view).
     */
    public function getUserHolidaysByMonth(int $year, int $month): JsonResponse
    {
        $userId = (int) Auth::id();
        $data = $this->service->getUserHolidaysByMonth($userId, $year, $month);

        return response()->success($data, 'User holidays retrieved successfully');
    }

    /**
     * Import Holidays
     *
     * Import multiple holidays into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (Auth::user()->denies('import holidays')) {
            return response()->forbidden('Permission denied for import holidays.');
        }
        $this->service->importHolidays($request->file('file'));

        return response()->success(null, 'Holidays imported successfully');
    }

    /**
     * Export Holidays
     *
     * Export a list of holidays to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (Auth::user()->denies('export holidays')) {
            return response()->forbidden('Permission denied for export holidays.');
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
            $userId = $validated['user_id'] ?? Auth::id();
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
                    'holidays_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Holidays Export Is Ready',
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
        if (Auth::user()->denies('import holidays')) {
            return response()->forbidden('Permission denied for downloading holidays import template.');
        }
        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
