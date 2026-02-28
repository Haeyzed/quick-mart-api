<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OnboardingChecklistTemplateResource;
use App\Models\OnboardingChecklistTemplate;
use App\Services\OnboardingChecklistTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class OnboardingChecklistTemplateController extends Controller
{
    public function __construct(private readonly OnboardingChecklistTemplateService $service) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $items = $this->service->getPaginated(
            $request->validate(['search' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(OnboardingChecklistTemplateResource::collection($items), 'Templates retrieved successfully');
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success($this->service->getOptions(), 'Options retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate(['name' => ['required', 'string', 'max:255'], 'is_default' => ['nullable', 'boolean']]);
        $model = $this->service->create($data);

        return response()->success(new OnboardingChecklistTemplateResource($model), 'Template created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new OnboardingChecklistTemplateResource($onboarding_checklist_template->load('items')), 'Template retrieved successfully');
    }

    public function update(Request $request, OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate(['name' => ['sometimes', 'required', 'string', 'max:255'], 'is_default' => ['nullable', 'boolean']]);
        $updated = $this->service->update($onboarding_checklist_template, $data);

        return response()->success(new OnboardingChecklistTemplateResource($updated), 'Template updated successfully');
    }

    public function destroy(OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($onboarding_checklist_template);

        return response()->success(null, 'Template deleted successfully');
    }
}
