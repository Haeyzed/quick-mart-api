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

class EmployeeOnboardingController extends Controller
{
    public function __construct(private readonly EmployeeOnboardingService $service) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $items = $this->service->getPaginated(
            $request->validate(['employee_id' => ['nullable', 'integer', 'exists:employees,id'], 'status' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(EmployeeOnboardingResource::collection($items), 'Employee onboardings retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'onboarding_checklist_template_id' => ['required', 'integer', 'exists:onboarding_checklist_templates,id'],
        ]);
        $model = $this->service->startOnboarding((int) $data['employee_id'], (int) $data['onboarding_checklist_template_id']);

        return response()->success(new EmployeeOnboardingResource($model), 'Onboarding started successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(EmployeeOnboarding $employee_onboarding): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new EmployeeOnboardingResource($employee_onboarding->load(['employee', 'template', 'items.checklistItem'])), 'Employee onboarding retrieved successfully');
    }

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

    public function destroy(EmployeeOnboarding $employee_onboarding): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($employee_onboarding);

        return response()->success(null, 'Employee onboarding deleted successfully');
    }

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
