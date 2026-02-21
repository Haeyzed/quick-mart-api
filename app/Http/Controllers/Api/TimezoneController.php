<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Timezones\StoreTimezoneRequest;
use App\Http\Requests\Timezones\TimezoneBulkActionRequest;
use App\Http\Requests\Timezones\UpdateTimezoneRequest;
use App\Http\Resources\TimezoneResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Timezone;
use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class TimezoneController
 *
 * API Controller for Timezone listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to TimezoneService.
 *
 * @tags Timezone Management
 */
class TimezoneController extends Controller
{
    /**
     * TimezoneController constructor.
     *
     * @param  TimezoneService  $service  Service handling timezone business logic.
     */
    public function __construct(
        private readonly TimezoneService $service
    ) {}

    /**
     * List Timezones
     *
     * Display a paginated listing of timezones. Supports searching and filtering by country.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for viewing timezones list.');
        }

        $timezones = $this->service->getPaginatedTimezones(
            $request->validate([
                /**
                 * Search term to filter timezones by name.
                 *
                 * @example "America"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by country ID.
                 *
                 * @example 1
                 */
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            TimezoneResource::collection($timezones),
            'Timezones retrieved successfully'
        );
    }

    /**
     * Get timezone options for select components (grouped by region).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for viewing timezone options.');
        }

        return response()->success($this->service->getOptions(), 'Timezone options retrieved successfully');
    }

    /**
     * Show Timezone
     *
     * Display the specified timezone.
     */
    public function show(Timezone $timezone): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for view timezone.');
        }

        return response()->success(
            new TimezoneResource($timezone->load('country')),
            'Timezone details retrieved successfully'
        );
    }

    /**
     * Create Timezone
     */
    public function store(StoreTimezoneRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create timezones')) {
            return response()->forbidden('Permission denied for create timezone.');
        }

        $timezone = $this->service->createTimezone($request->validated());

        return response()->success(
            new TimezoneResource($timezone),
            'Timezone created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update Timezone
     */
    public function update(UpdateTimezoneRequest $request, Timezone $timezone): JsonResponse
    {
        if (auth()->user()->denies('update timezones')) {
            return response()->forbidden('Permission denied for update timezone.');
        }

        $updatedTimezone = $this->service->updateTimezone($timezone, $request->validated());

        return response()->success(
            new TimezoneResource($updatedTimezone),
            'Timezone updated successfully'
        );
    }

    /**
     * Delete Timezone
     */
    public function destroy(Timezone $timezone): JsonResponse
    {
        if (auth()->user()->denies('delete timezones')) {
            return response()->forbidden('Permission denied for delete timezone.');
        }

        $this->service->deleteTimezone($timezone);

        return response()->success(null, 'Timezone deleted successfully');
    }

    /**
     * Bulk Delete Timezones
     */
    public function bulkDestroy(TimezoneBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete timezones')) {
            return response()->forbidden('Permission denied for bulk delete timezones.');
        }

        $count = $this->service->bulkDeleteTimezones($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} timezones"
        );
    }

    /**
     * Import Timezones
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import timezones')) {
            return response()->forbidden('Permission denied for import timezones.');
        }

        $this->service->importTimezones($request->file('file'));

        return response()->success(null, 'Timezones imported successfully');
    }

    /**
     * Export Timezones
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export timezones')) {
            return response()->forbidden('Permission denied for export timezones.');
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
                    'timezones_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Timezone Export Is Ready',
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
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import timezones')) {
            return response()->forbidden('Permission denied for downloading timezones import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
