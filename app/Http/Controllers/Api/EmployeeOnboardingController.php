<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EmployeeOnboardingResource;
use App\Models\EmployeeOnboarding;
use App\Models\EmployeeOnboardingItem;
use App\Services\EmployeeOnboardingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class EmployeeOnboardingController
 *
 * API Controller for employee onboarding workflows. Start onboarding from a template,
 * update status, complete checklist items, and delete onboardings.
 *
 * @tags HRM Management
 */
class EmployeeOnboardingController extends Controller
{
    /**
     * EmployeeOnboardingController constructor.
     */
    public function __construct(
        private readonly EmployeeOnboardingService $service
    )
    {
    }

    /**
     * List Employee Onboardings
     *
     * Display a paginated listing of employee onboardings. Filter by employee_id and status.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /** @example 5 */
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                /** @example "in_progress" */
                'status' => ['nullable', 'string'],
            ]),
            /** @default 10 */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(EmployeeOnboardingResource::collection($items), 'Employee onboardings retrieved successfully');
    }

    /**
     * Start Onboarding
     *
     * Create an onboarding for an employee from a checklist template; checklist items are created automatically.
     */
    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'onboarding_checklist_template_id' => ['required', 'integer', 'exists:onboarding_checklist_templates,id'],
        ]);
        $model = $this->service->startOnboarding((int)$data['employee_id'], (int)$data['onboarding_checklist_template_id']);

        return response()->success(new EmployeeOnboardingResource($model), 'Onboarding started successfully', ResponseAlias::HTTP_CREATED);
    }

    /**
     * Show Employee Onboarding
     *
     * Retrieve an onboarding with employee, template, and checklist item progress.
     */
    public function show(EmployeeOnboarding $employee_onboarding): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new EmployeeOnboardingResource($employee_onboarding->load(['employee', 'template', 'items.checklistItem'])), 'Employee onboarding retrieved successfully');
    }

    /**
     * Update Employee Onboarding
     *
     * Update onboarding status (e.g. pending, in_progress, completed).
     */
    public function update(Request $request, EmployeeOnboarding $employee_onboarding): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed'],
        ]);
        $updated = $this->service->update($employee_onboarding, $data);

        return response()->success(new EmployeeOnboardingResource($updated), 'Employee onboarding updated successfully');
    }

    /**
     * Delete Employee Onboarding
     *
     * Remove the onboarding and its checklist item progress.
     */
    public function destroy(EmployeeOnboarding $employee_onboarding): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($employee_onboarding);

        return response()->success(null, 'Employee onboarding deleted successfully');
    }

    /**
     * Complete Checklist Item
     *
     * Mark a single onboarding checklist item as completed; optionally auto-complete onboarding when all items are done.
     */
    public function completeItem(Request $request, EmployeeOnboardingItem $employee_onboarding_item): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $notes = $request->input('notes');
        $updated = $this->service->completeItem($employee_onboarding_item, $notes);

        return response()->success([
            'item' => $updated,
            'employee_onboarding' => new EmployeeOnboardingResource($updated->employeeOnboarding->load(['employee', 'template', 'items.checklistItem'])),
        ], 'Checklist item completed');
    }
}
