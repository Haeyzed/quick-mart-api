<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Designations\StoreDesignationRequest;
use App\Http\Requests\Designations\UpdateDesignationRequest;
use App\Http\Requests\Designations\DesignationBulkActionRequest;
use App\Http\Resources\DesignationResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Designation;
use App\Services\DesignationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class DesignationController
 *
 * API Controller for Designation CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to DesignationService.
 *
 * @tags HRM Management
 */
class DesignationController extends Controller
{
    /**
     * DesignationController constructor.
     */
    public function __construct(
        private readonly DesignationService $service
    ) {}

    /**
     * List Designations
     *
     * Display a paginated listing of designations. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view designations')) {
            return response()->forbidden('Permission denied for viewing designations list.');
        }

        $designations = $this->service->getPaginatedDesignations(
            $request->validate([
                /**
                 * Search term to filter designations by name.
                 *
                 * @example "Manager"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter designations starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter designations up to this date.
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
            DesignationResource::collection($designations),
            'Designations retrieved successfully'
        );
    }

    /**
     * Get Designation Options
     *
     * Retrieve a simplified list of active designations for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view designations')) {
            return response()->forbidden('Permission denied for viewing designation options.');
        }

        return response()->success($this->service->getOptions(), 'Designation options retrieved successfully');
    }

    /**
     * Create Designation
     *
     * Store a newly created designation in the system.
     */
    public function store(StoreDesignationRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create designations')) {
            return response()->forbidden('Permission denied for create designation.');
        }

        $designation = $this->service->createDesignation($request->validated());

        return response()->success(
            new DesignationResource($designation),
            'Designation created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Designation
     *
     * Retrieve the details of a specific designation by its ID.
     */
    public function show(Designation $designation): JsonResponse
    {
        if (auth()->user()->denies('view designation details')) {
            return response()->forbidden('Permission denied for view designation.');
        }

        return response()->success(
            new DesignationResource($designation),
            'Designation details retrieved successfully'
        );
    }

    /**
     * Update Designation
     *
     * Update the specified designation's information.
     */
    public function update(UpdateDesignationRequest $request, Designation $designation): JsonResponse
    {
        if (auth()->user()->denies('update designations')) {
            return response()->forbidden('Permission denied for update designation.');
        }

        $updatedDesignation = $this->service->updateDesignation($designation, $request->validated());

        return response()->success(
            new DesignationResource($updatedDesignation),
            'Designation updated successfully'
        );
    }

    /**
     * Delete Designation
     *
     * Remove the specified designation from storage.
     */
    public function destroy(Designation $designation): JsonResponse
    {
        if (auth()->user()->denies('delete designations')) {
            return response()->forbidden('Permission denied for delete designation.');
        }

        $this->service->deleteDesignation($designation);

        return response()->success(null, 'Designation deleted successfully');
    }

    /**
     * Bulk Delete Designations
     *
     * Delete multiple designations simultaneously using an array of IDs.
     */
    public function bulkDestroy(DesignationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete designations')) {
            return response()->forbidden('Permission denied for bulk delete designations.');
        }

        $count = $this->service->bulkDeleteDesignations($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} designations"
        );
    }

    /**
     * Bulk Activate Designations
     *
     * Set the active status of multiple designations to true.
     */
    public function bulkActivate(DesignationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update designations')) {
            return response()->forbidden('Permission denied for bulk update designations.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} designations activated"
        );
    }

    /**
     * Bulk Deactivate Designations
     *
     * Set the active status of multiple designations to false.
     */
    public function bulkDeactivate(DesignationBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update designations')) {
            return response()->forbidden('Permission denied for bulk update designations.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} designations deactivated"
        );
    }

    /**
     * Import Designations
     *
     * Import multiple designations into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import designations')) {
            return response()->forbidden('Permission denied for import designations.');
        }

        $this->service->importDesignations($request->file('file'));

        return response()->success(null, 'Designations imported successfully');
    }

    /**
     * Export Designations
     *
     * Export a list of designations to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export designations')) {
            return response()->forbidden('Permission denied for export designations.');
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
                    'designations_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Designation Export Is Ready',
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
     * Download Designation Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import designations')) {
            return response()->forbidden('Permission denied for downloading designations import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
