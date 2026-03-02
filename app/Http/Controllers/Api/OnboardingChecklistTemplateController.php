<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardingChecklistTemplates\OnboardingChecklistTemplateBulkActionRequest;
use App\Http\Resources\OnboardingChecklistTemplateResource;
use App\Models\OnboardingChecklistTemplate;
use App\Services\OnboardingChecklistTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class OnboardingChecklistTemplateController
 *
 * API Controller for Onboarding Checklist Template CRUD and bulk operations.
 * Handles authorization via permissions and delegates logic to OnboardingChecklistTemplateService.
 *
 * @tags HRM Management
 */
class OnboardingChecklistTemplateController extends Controller
{
    /**
     * OnboardingChecklistTemplateController constructor.
     */
    public function __construct(
        private readonly OnboardingChecklistTemplateService $service
    ) {}

    /**
     * List Onboarding Checklist Templates
     *
     * Display a paginated listing of onboarding checklist templates.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Search term to filter templates by name.
                 *
                 * @example "New Hire"
                 */
                'search' => ['nullable', 'string'],
            ]),
            /**
             * Amount of items per page.
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(OnboardingChecklistTemplateResource::collection($items), 'Templates retrieved successfully');
    }

    /**
     * Template Options
     *
     * Return a list of onboarding checklist template options for dropdowns.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success($this->service->getOptions(), 'Options retrieved successfully');
    }

    /**
     * Create Onboarding Checklist Template
     *
     * Store a newly created onboarding checklist template in the system.
     */
    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example "New Hire Checklist" */
            'name' => ['required', 'string', 'max:255'],
            /** @example false */
            'is_default' => ['nullable', 'boolean'],
        ]);
        $model = $this->service->create($data);

        return response()->success(new OnboardingChecklistTemplateResource($model), 'Template created successfully', ResponseAlias::HTTP_CREATED);
    }

    /**
     * Show Onboarding Checklist Template
     *
     * Retrieve the details of a specific onboarding checklist template by its ID.
     */
    public function show(OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new OnboardingChecklistTemplateResource($onboarding_checklist_template->load('items')), 'Template retrieved successfully');
    }

    /**
     * Update Onboarding Checklist Template
     *
     * Update the specified onboarding checklist template's information.
     */
    public function update(Request $request, OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example "New Hire Checklist" */
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            /** @example false */
            'is_default' => ['nullable', 'boolean'],
        ]);
        $updated = $this->service->update($onboarding_checklist_template, $data);

        return response()->success(new OnboardingChecklistTemplateResource($updated), 'Template updated successfully');
    }

    /**
     * Delete Onboarding Checklist Template
     *
     * Remove the specified onboarding checklist template from storage.
     */
    public function destroy(OnboardingChecklistTemplate $onboarding_checklist_template): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($onboarding_checklist_template);

        return response()->success(null, 'Template deleted successfully');
    }

    /**
     * Bulk Delete Onboarding Checklist Templates
     *
     * Delete multiple onboarding checklist templates simultaneously using an array of IDs.
     */
    public function bulkDestroy(OnboardingChecklistTemplateBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied for bulk delete templates.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} templates"
        );
    }
}
