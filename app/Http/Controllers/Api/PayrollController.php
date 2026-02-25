<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\PayrollStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Payrolls\GeneratePayrollDataRequest;
use App\Http\Requests\Payrolls\BulkStorePayrollRequest;
use App\Http\Requests\Payrolls\StorePayrollRequest;
use App\Http\Requests\Payrolls\UpdatePayrollRequest;
use App\Http\Requests\Payrolls\PayrollBulkActionRequest;
use App\Http\Resources\PayrollResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Payroll;
use App\Services\PayrollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class PayrollController
 *
 * API Controller for Payroll CRUD, Bulk Generation, and Data Processing.
 * Handles authorization via Policy and delegates logic to PayrollService.
 *
 * @tags HRM Management
 */
class PayrollController extends Controller
{
    /**
     * PayrollController constructor.
     */
    public function __construct(
        private readonly PayrollService $service
    ) {}

    /**
     * List Payrolls
     *
     * Display a paginated listing of payroll records. Includes dynamically calculated stats like leaves and attendance.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view payrolls')) {
            return response()->forbidden('Permission denied for viewing payroll list.');
        }

        $payrolls = $this->service->getPaginatedPayrolls(
            $request->validate([
                /**
                 * Search term to filter payrolls by reference number or employee name.
                 *
                 * @example "PR-123"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by status.
                 *
                 * @example "paid"
                 */
                'status' => ['nullable', 'string', 'in:paid,draft'],
                /**
                 * Filter by specific employee ID.
                 *
                 * @example 5
                 */
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                /**
                 * Filter by month.
                 *
                 * @example "2024-12"
                 */
                'month' => ['nullable', 'string'],
                /**
                 * Filter payrolls starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter payrolls up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            PayrollResource::collection($payrolls),
            'Payrolls retrieved successfully'
        );
    }

    /**
     * Generate Payroll
     *
     * Fetch prospective calculation data (expenses, target commissions, basic salary, overlaps)
     * required to populate the generation form.
     */
    public function generateData(GeneratePayrollDataRequest $request): JsonResponse
    {
        if (auth()->user()->denies('generate payrolls')) {
            return response()->forbidden('Permission denied for generating payroll data.');
        }

        $data = $this->service->getGenerationData(
            $request->input('month'),
            $request->input('warehouse_id'),
            $request->input('employee_ids')
        );

        return response()->success($data, 'Payroll generation data retrieved successfully');
    }

    /**
     * Process Bulk Payrolls
     *
     * Submits an array of payrolls from the generation screen. Handles upserting records,
     * deducting account balances, creating payment logs, and sending notification emails.
     */
    public function bulkProcess(BulkStorePayrollRequest $request): JsonResponse
    {
        if (auth()->user()->denies('generate payrolls')) {
            return response()->forbidden('Permission denied for processing payrolls.');
        }

        // Safely extract enum without throwing fatal errors on invalid input
        $statusValue = $request->input('payroll_group_status');
        $payrollStatus = $statusValue ? PayrollStatusEnum::tryFrom($statusValue) : PayrollStatusEnum::DRAFT;

        $this->service->processBulkPayrolls(
            $request->input('month'),
            $request->input('payrolls'),
            $request->input('account_id'),
            $payrollStatus ?? PayrollStatusEnum::DRAFT
        );

        return response()->success(null, 'All payrolls processed successfully!');
    }

    /**
     * Create Payroll
     *
     * Store a newly created individual payroll record in the system.
     */
    public function store(StorePayrollRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create payrolls')) {
            return response()->forbidden('Permission denied for creating payroll.');
        }

        $payroll = $this->service->createPayroll($request->validated());

        return response()->success(
            new PayrollResource($payroll),
            'Payroll created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Payroll
     *
     * Retrieve the details of a specific payroll record by its ID.
     */
    public function show(Payroll $payroll): JsonResponse
    {
        if (auth()->user()->denies('view payrolls')) {
            return response()->forbidden('Permission denied for viewing payroll.');
        }

        return response()->success(
            new PayrollResource($payroll->load(['employee', 'user', 'account'])),
            'Payroll details retrieved successfully'
        );
    }

    /**
     * Update Payroll
     *
     * Update the specified payroll record's information.
     */
    public function update(UpdatePayrollRequest $request, Payroll $payroll): JsonResponse
    {
        if (auth()->user()->denies('update payrolls')) {
            return response()->forbidden('Permission denied for updating payroll.');
        }

        $updatedPayroll = $this->service->updatePayroll($payroll, $request->validated());

        return response()->success(
            new PayrollResource($updatedPayroll),
            'Payroll updated successfully'
        );
    }

    /**
     * Delete Payroll
     *
     * Remove the specified payroll record from storage.
     */
    public function destroy(Payroll $payroll): JsonResponse
    {
        if (auth()->user()->denies('delete payrolls')) {
            return response()->forbidden('Permission denied for deleting payroll.');
        }

        $this->service->deletePayroll($payroll);

        return response()->success(null, 'Payroll deleted successfully');
    }

    /**
     * Bulk Delete Payrolls
     *
     * Delete multiple payroll records simultaneously using an array of IDs.
     */
    public function bulkDestroy(PayrollBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete payrolls')) {
            return response()->forbidden('Permission denied for bulk delete payrolls.');
        }

        $count = $this->service->bulkDeletePayrolls($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} payroll records"
        );
    }

    /**
     * Bulk Mark Paid
     *
     * Set the status of multiple payroll records to 'paid'.
     */
    public function bulkMarkPaid(PayrollBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update payrolls')) {
            return response()->forbidden('Permission denied for bulk update payrolls.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], PayrollStatusEnum::PAID->value);

        return response()->success(
            ['updated_count' => $count],
            "{$count} payroll records marked as paid successfully"
        );
    }

    /**
     * Import Payrolls
     *
     * Import multiple payroll records into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import payrolls')) {
            return response()->forbidden('Permission denied for import payrolls.');
        }

        $this->service->importPayrolls($request->file('file'));

        return response()->success(null, 'Payrolls imported successfully');
    }

    /**
     * Export Payrolls
     *
     * Export a list of payroll records to an Excel or PDF file.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export payrolls')) {
            return response()->forbidden('Permission denied for export payrolls.');
        }

        $validated = $request->validated();
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()->download(Storage::disk('public')->path($path))->deleteFileAfterSend();
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
                    'payrolls_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Payroll Export Is Ready',
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
        if (auth()->user()->denies('import payrolls')) {
            return response()->forbidden('Permission denied for downloading template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}
