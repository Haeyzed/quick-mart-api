<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollRuns\StorePayrollRunRequest;
use App\Http\Requests\PayrollRuns\UpdatePayrollRunRequest;
use App\Http\Resources\PayrollRunResource;
use App\Models\PayrollRun;
use App\Services\PayrollRunService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class PayrollRunController
 *
 * API Controller for Payroll Run CRUD and generate-entries operations.
 * Handles authorization via permissions and delegates logic to PayrollRunService.
 *
 * @tags HRM Management
 */
class PayrollRunController extends Controller
{
    /**
     * PayrollRunController constructor.
     */
    public function __construct(
        private readonly PayrollRunService $service
    )
    {
    }

    /**
     * List Payroll Runs
     *
     * Display a paginated listing of payroll runs. Supports filtering by status, year and month.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll runs.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Filter by run status (draft, processing, completed).
                 *
                 * @example "draft"
                 */
                'status' => ['nullable', 'string', 'in:draft,processing,completed'],
                /**
                 * Filter by year.
                 *
                 * @example 2024
                 */
                'year' => ['nullable', 'integer'],
                /**
                 * Filter by month in YYYY-MM format.
                 *
                 * @example "2024-01"
                 */
                'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
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
            PayrollRunResource::collection($items),
            'Payroll runs retrieved successfully'
        );
    }

    /**
     * Payroll Run Options
     *
     * Return a list of payroll run options (id and month/year label) for dropdowns.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll run options.');
        }

        return response()->success($this->service->getOptions(), 'Payroll run options retrieved successfully');
    }

    /**
     * Create Payroll Run
     *
     * Store a newly created payroll run in the system.
     */
    public function store(StorePayrollRunRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create payroll runs')) {
            return response()->forbidden('Permission denied for creating payroll run.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new PayrollRunResource($model->load('generatedByUser')),
            'Payroll run created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Payroll Run
     *
     * Retrieve the details of a specific payroll run by its ID.
     */
    public function show(PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll run.');
        }

        return response()->success(
            new PayrollRunResource($payroll_run->load(['generatedByUser', 'entries.employee'])),
            'Payroll run details retrieved successfully'
        );
    }

    /**
     * Update Payroll Run
     *
     * Update the specified payroll run's information.
     */
    public function update(UpdatePayrollRunRequest $request, PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('update payroll runs')) {
            return response()->forbidden('Permission denied for updating payroll run.');
        }

        $updated = $this->service->update($payroll_run, $request->validated());

        return response()->success(
            new PayrollRunResource($updated->load('generatedByUser')),
            'Payroll run updated successfully'
        );
    }

    /**
     * Delete Payroll Run
     *
     * Remove the specified payroll run and its entries from storage.
     */
    public function destroy(PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('delete payroll runs')) {
            return response()->forbidden('Permission denied for deleting payroll run.');
        }

        $this->service->delete($payroll_run);

        return response()->success(null, 'Payroll run deleted successfully');
    }

    /**
     * Generate Payroll Entries
     *
     * Generate payroll entries for all active employees for the given payroll run.
     */
    public function generateEntries(PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('create payroll runs')) {
            return response()->forbidden('Permission denied for generating payroll entries.');
        }

        $run = $this->service->generateEntries($payroll_run);

        return response()->success(
            new PayrollRunResource($run),
            'Payroll entries generated successfully'
        );
    }
}
