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
 *
 * @group Holiday Management
 */
class HolidayController extends Controller
{
    public function __construct(
        private readonly HolidayService $service
    ) {
    }

    /**
     * Display a paginated listing of holidays.
     */
    public function index(Request $request): JsonResponse
    {
        if (Auth::user()->denies('view holidays')) {
            return response()->forbidden('Permission denied for viewing holidays list.');
        }

        $filters = $request->all();
        $holidays = $this->service->getPaginatedHolidays(
            $filters,
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            HolidayResource::collection($holidays),
            'Holidays retrieved successfully'
        );
    }

    /**
     * Store a newly created holiday.
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
     * Display the specified holiday.
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
     * Update the specified holiday.
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
     * Remove the specified holiday.
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
     * Bulk delete holidays.
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
     * Approve a holiday request.
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
     * Get user holidays for a specific month.
     */
    public function getUserHolidaysByMonth(int $year, int $month): JsonResponse
    {
        $userId = (int) Auth::id();
        $data = $this->service->getUserHolidaysByMonth($userId, $year, $month);

        return response()->success($data, 'User holidays retrieved successfully');
    }

    /**
     * Import holidays from Excel/CSV.
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
     * Export holidays to Excel or PDF.
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
     * Download holidays import sample template.
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
