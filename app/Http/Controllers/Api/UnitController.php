<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Units\StoreUnitRequest;
use App\Http\Requests\Units\UnitBulkActionRequest;
use App\Http\Requests\Units\UpdateUnitRequest;
use App\Http\Resources\UnitResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Unit;
use App\Models\User;
use App\Services\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class UnitController
 *
 * API Controller for Unit CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to UnitService.
 *
 * @tags Unit Management
 */
class UnitController extends Controller
{
    /**
     * UnitController constructor.
     */
    public function __construct(
        private readonly UnitService $service
    ) {}

    /**
     * List Units
     *
     * Display a paginated listing of units. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view units')) {
            return response()->forbidden('Permission denied for viewing units list.');
        }

        $units = $this->service->getPaginatedUnits(
            $request->validate([
                /**
                 * Search term to filter units by name or code.
                 *
                 * @example "kg"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'status' => ['nullable', 'boolean'],
                /**
                 * Filter units starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter units up to this date.
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
            UnitResource::collection($units),
            'Units retrieved successfully'
        );
    }

    /**
     * Get Unit Options
     *
     * Retrieve a simplified list of active units for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view units')) {
            return response()->forbidden('Permission denied for viewing unit options.');
        }

        return response()->success($this->service->getOptions(), 'Unit options retrieved successfully');
    }

    /**
     * Get Base Units
     *
     * Retrieve a list of active base units (no base_unit) for use when creating or editing derived units.
     */
    public function getBaseUnits(): JsonResponse
    {
        if (auth()->user()->denies('view units')) {
            return response()->forbidden('Permission denied for viewing base units.');
        }

        return response()->success($this->service->getBaseUnits(), 'Base units retrieved successfully');
    }

    /**
     * Create Unit
     *
     * Store a newly created unit in the system.
     */
    public function store(StoreUnitRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create units')) {
            return response()->forbidden('Permission denied for create unit.');
        }

        $unit = $this->service->createUnit($request->validated());

        return response()->success(
            new UnitResource($unit),
            'Unit created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Unit
     *
     * Retrieve the details of a specific unit by its ID.
     */
    public function show(Unit $unit): JsonResponse
    {
        if (auth()->user()->denies('view unit details')) {
            return response()->forbidden('Permission denied for view unit.');
        }

        return response()->success(
            new UnitResource($unit->fresh('baseUnitRelation')),
            'Unit details retrieved successfully'
        );
    }

    /**
     * Update Unit
     *
     * Update the specified unit's information.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): JsonResponse
    {
        if (auth()->user()->denies('update units')) {
            return response()->forbidden('Permission denied for update unit.');
        }

        $updatedUnit = $this->service->updateUnit($unit, $request->validated());

        return response()->success(
            new UnitResource($updatedUnit),
            'Unit updated successfully'
        );
    }

    /**
     * Delete Unit
     *
     * Remove the specified unit from storage.
     */
    public function destroy(Unit $unit): JsonResponse
    {
        if (auth()->user()->denies('delete units')) {
            return response()->forbidden('Permission denied for delete unit.');
        }

        $this->service->deleteUnit($unit);

        return response()->success(null, 'Unit deleted successfully');
    }

    /**
     * Bulk Delete Units
     *
     * Delete multiple units simultaneously using an array of IDs.
     */
    public function bulkDestroy(UnitBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete units')) {
            return response()->forbidden('Permission denied for bulk delete units.');
        }

        $count = $this->service->bulkDeleteUnits($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} units"
        );
    }

    /**
     * Bulk Activate Units
     *
     * Set the active status of multiple units to true.
     */
    public function bulkActivate(UnitBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update units')) {
            return response()->forbidden('Permission denied for bulk update units.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} units activated"
        );
    }

    /**
     * Bulk Deactivate Units
     *
     * Set the active status of multiple units to false.
     */
    public function bulkDeactivate(UnitBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update units')) {
            return response()->forbidden('Permission denied for bulk update units.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} units deactivated"
        );
    }

    /**
     * Import Units
     *
     * Import multiple units into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import units')) {
            return response()->forbidden('Permission denied for import units.');
        }

        $this->service->importUnits($request->file('file'));

        return response()->success(null, 'Units imported successfully');
    }

    /**
     * Export Units
     *
     * Export a list of units to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export units')) {
            return response()->forbidden('Permission denied for export units.');
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
                    'units_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Unit Export Is Ready',
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
        if (auth()->user()->denies('import units')) {
            return response()->forbidden('Permission denied for downloading units import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
