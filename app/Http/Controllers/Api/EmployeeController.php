<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employees\EmployeeBulkActionRequest;
use App\Http\Requests\Employees\StoreEmployeeRequest;
use App\Http\Requests\Employees\UpdateEmployeeRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\EmployeeResource;
use App\Mail\ExportMail;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class EmployeeController
 *
 * API Controller for Employee CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to EmployeeService.
 *
 * @tags HRM Management
 */
class EmployeeController extends Controller
{
    /**
     * EmployeeController constructor.
     */
    public function __construct(
        private readonly EmployeeService $service
    ) {}

    /**
     * List Employees
     *
     * Display a paginated listing of employees. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employees')) {
            return response()->forbidden('Permission denied for viewing employees list.');
        }

        $employees = $this->service->getPaginatedEmployees(
            $request->validate([
                /**
                 * Search term to filter employees by name, email, phone, or staff ID.
                 *
                 * @example "Jane Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter by associated department ID.
                 *
                 * @example 2
                 */
                'department_id' => ['nullable', 'integer', 'exists:departments,id'],
                /**
                 * Filter employees starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter employees up to this date.
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
            EmployeeResource::collection($employees),
            'Employees retrieved successfully'
        );
    }

    /**
     * Get Employee Options
     *
     * Retrieve a simplified list of active employees for use in dropdowns or select components.
     */
    public function options(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employees')) {
            return response()->forbidden('Permission denied for viewing employees options.');
        }
        $warehouseId = $request->input('warehouse_id');

        return response()->success($this->service->getOptions($warehouseId), 'Employee options retrieved successfully');
    }

    /**
     * Create Employee
     *
     * Store a newly created employee in the system.
     */
    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create employees')) {
            return response()->forbidden('Permission denied for create employee.');
        }

        $employee = $this->service->createEmployee($request->validated());

        return response()->success(
            new EmployeeResource($employee),
            'Employee created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Employee
     *
     * Retrieve the details of a specific employee by its ID.
     */
    public function show(Employee $employee): JsonResponse
    {
        if (auth()->user()->denies('view employee details')) {
            return response()->forbidden('Permission denied for view employee.');
        }

        return response()->success(
            new EmployeeResource($employee->load(['department', 'designation', 'shift', 'country', 'state', 'city'])),
            'Employee details retrieved successfully'
        );
    }

    /**
     * Update Employee
     *
     * Update the specified employee's information.
     */
    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        if (auth()->user()->denies('update employees')) {
            return response()->forbidden('Permission denied for update employee.');
        }

        $updatedEmployee = $this->service->updateEmployee($employee, $request->validated());

        return response()->success(
            new EmployeeResource($updatedEmployee->load(['department', 'designation', 'shift', 'country', 'state', 'city'])),
            'Employee updated successfully'
        );
    }

    /**
     * Delete Employee
     *
     * Remove the specified employee from storage.
     */
    public function destroy(Employee $employee): JsonResponse
    {
        if (auth()->user()->denies('delete employees')) {
            return response()->forbidden('Permission denied for delete employee.');
        }

        $this->service->deleteEmployee($employee);

        return response()->success(null, 'Employee deleted successfully');
    }

    /**
     * Bulk Delete Employees
     *
     * Delete multiple employees simultaneously using an array of IDs.
     */
    public function bulkDestroy(EmployeeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete employees')) {
            return response()->forbidden('Permission denied for bulk delete employees.');
        }

        $count = $this->service->bulkDeleteEmployees($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} employees"
        );
    }

    /**
     * Bulk Activate Employees
     *
     * Set the active status of multiple employees to true.
     */
    public function bulkActivate(EmployeeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employees')) {
            return response()->forbidden('Permission denied for bulk update employees.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} employees activated"
        );
    }

    /**
     * Bulk Deactivate Employees
     *
     * Set the active status of multiple employees to false.
     */
    public function bulkDeactivate(EmployeeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employees')) {
            return response()->forbidden('Permission denied for bulk update employees.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} employees deactivated"
        );
    }

    /**
     * Import Employees
     *
     * Import multiple employees into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import employees')) {
            return response()->forbidden('Permission denied for import employees.');
        }

        $this->service->importEmployees($request->file('file'));

        return response()->success(null, 'Employees imported successfully');
    }

    /**
     * Export Employees
     *
     * Export a list of employees to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export employees')) {
            return response()->forbidden('Permission denied for export employees.');
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

            if (!$user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (!$mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'employees_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Employee Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: ' . $user->email
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
        if (auth()->user()->denies('import employees')) {
            return response()->forbidden('Permission denied for downloading employees import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
