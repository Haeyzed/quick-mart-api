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

class PayrollRunController extends Controller
{
    public function __construct(
        private readonly PayrollRunService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll runs.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'status' => ['nullable', 'string', 'in:draft,processing,completed'],
                'year' => ['nullable', 'integer'],
                'month' => ['nullable', 'string', 'regex:/^\d{4}-\d{2}$/'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            PayrollRunResource::collection($items),
            'Payroll runs retrieved successfully'
        );
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view payroll runs')) {
            return response()->forbidden('Permission denied for viewing payroll run options.');
        }

        return response()->success($this->service->getOptions(), 'Payroll run options retrieved successfully');
    }

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

    public function destroy(PayrollRun $payroll_run): JsonResponse
    {
        if (auth()->user()->denies('delete payroll runs')) {
            return response()->forbidden('Permission denied for deleting payroll run.');
        }

        $this->service->delete($payroll_run);

        return response()->success(null, 'Payroll run deleted successfully');
    }

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
