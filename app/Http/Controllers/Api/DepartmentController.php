<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Departments\StoreDepartmentRequest;
use App\Http\Requests\Departments\UpdateDepartmentRequest;
use App\Http\Requests\Departments\DepartmentBulkActionRequest;
use App\Http\Resources\DepartmentResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class DepartmentController
 *
 * API Controller for Department CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to DepartmentService.
 *
 * @tags HRM Management
 */
class DepartmentController extends Controller
{
    /**
     * DepartmentController constructor.
     */
    public function __construct(
        private readonly DepartmentService $service
    ) {}

    /**
     * List Departments
     *
     * Display a paginated listing of departments. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view departments')) {
            return response()->forbidden('Permission denied for viewing departments list.');
        }

        $departments = $this->service->getPaginatedDepartments(
            $request->validate([
                /**
                 * Search term to filter departments by name.
                 *
                 * @example "Marketing"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter departments starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter departments up to this date.
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
            DepartmentResource::collection($departments),
            'Departments retrieved successfully'
        );
    }

    /**
     * Get Department Options
     *
     * Retrieve a simplified list of active departments for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view departments')) {
            return response()->forbidden('Permission denied for viewing department options.');
        }

        return response()->success($this->service->getOptions(), 'Department options retrieved successfully');
    }

    /**
     * Create Department
     *
     * Store a newly created department in the system.
     */
    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create departments')) {
            return response()->forbidden('Permission denied for create department.');
        }

        $department = $this->service->createDepartment($request->validated());

        return response()->success(
            new DepartmentResource($department),
            'Department created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Department
     *
     * Retrieve the details of a specific department by its ID.
     */
    public function show(Department $department): JsonResponse
    {
        if (auth()->user()->denies('view departments')) {
            return response()->forbidden('Permission denied for view department.');
        }

        return response()->success(
            new DepartmentResource($department),
            'Department details retrieved successfully'
        );
    }

    /**
     * Update Department
     *
     * Update the specified department's information.
     */
    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        if (auth()->user()->denies('update departments')) {
            return response()->forbidden('Permission denied for update department.');
        }

        $updatedDepartment = $this->service->updateDepartment($department, $request->validated());

        return response()->success(
            new DepartmentResource($updatedDepartment),
            'Department updated successfully'
        );
    }

    /**
     * Delete Department
     *
     * Remove the specified department from storage.
     */
    public function destroy(Department $department): JsonResponse
    {
        if (auth()->user()->denies('delete departments')) {
            return response()->forbidden('Permission denied for delete department.');
        }

        $this->service->deleteDepartment($department);

        return response()->success(null, 'Department deleted successfully');
    }

    /**
     * Bulk Delete Departments
     *
     * Delete multiple departments simultaneously using an array of IDs.
     */
    public function bulkDestroy(DepartmentBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete departments')) {
            return response()->forbidden('Permission denied for bulk delete departments.');
        }

        $count = $this->service->bulkDeleteDepartments($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} departments"
        );
    }

    /**
     * Bulk Activate Departments
     *
     * Set the active status of multiple departments to true.
     */
    public function bulkActivate(DepartmentBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update departments')) {
            return response()->forbidden('Permission denied for bulk update departments.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} departments activated"
        );
    }

    /**
     * Bulk Deactivate Departments
     *
     * Set the active status of multiple departments to false.
     */
    public function bulkDeactivate(DepartmentBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update departments')) {
            return response()->forbidden('Permission denied for bulk update departments.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} departments deactivated"
        );
    }

    /**
     * Import Departments
     *
     * Import multiple departments into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import departments')) {
            return response()->forbidden('Permission denied for import departments.');
        }

        $this->service->importDepartments($request->file('file'));

        return response()->success(null, 'Departments imported successfully');
    }

    /**
     * Export Departments
     *
     * Export a list of departments to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export departments')) {
            return response()->forbidden('Permission denied for export departments.');
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
                    'departments_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Department Export Is Ready',
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
     * Download Department Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import departments')) {
            return response()->forbidden('Permission denied for downloading departments import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
