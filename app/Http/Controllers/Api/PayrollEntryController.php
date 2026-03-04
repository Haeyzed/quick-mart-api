<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PayrollEntries\UpdatePayrollEntryRequest;
use App\Http\Resources\PayrollEntryResource;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Services\PayrollEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class PayrollEntryController
 *
 * API Controller for Payroll Entry listing and update operations.
 * Handles authorization via permissions and delegates logic to PayrollEntryService.
 *
 * @tags HRM Management
 */
class PayrollEntryController extends Controller
{
    /**
     * PayrollEntryController constructor.
     */
    public function __construct(
        private readonly PayrollEntryService $service
    )
    {
    }

    /**
     * List Payroll Entries
     *
     * Display a paginated listing of payroll entries for a given payroll run.
     */
    public function index(Request $request, PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll entries.');
        }

        $entries = $this->service->getPaginatedByRun(
            $payroll_run->id,
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
            PayrollEntryResource::collection($entries),
            'Payroll entries retrieved successfully'
        );
    }

    /**
     * Show Payroll Entry
     *
     * Retrieve the details of a specific payroll entry by its ID.
     */
    public function show(PayrollEntry $payroll_entry): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll entry.');
        }

        return response()->success(
            new PayrollEntryResource($payroll_entry->load(['employee', 'items.salaryComponent'])),
            'Payroll entry details retrieved successfully'
        );
    }

    /**
     * Update Payroll Entry
     *
     * Update the specified payroll entry's information.
     */
    public function update(UpdatePayrollEntryRequest $request, PayrollEntry $payroll_entry): JsonResponse
    {
        if (auth()->user()->denies('update payroll runs')) {
            return response()->forbidden('Permission denied for updating payroll entry.');
        }

        $updated = $this->service->update($payroll_entry, $request->validated());

        return response()->success(
            new PayrollEntryResource($updated),
            'Payroll entry updated successfully'
        );
    }
}
