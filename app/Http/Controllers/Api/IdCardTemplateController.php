<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\IdCardTemplates\IdCardTemplateBulkActionRequest;
use App\Http\Requests\IdCardTemplates\StoreIdCardTemplateRequest;
use App\Http\Requests\IdCardTemplates\UpdateIdCardTemplateRequest;
use App\Http\Resources\IdCardTemplateResource;
use App\Models\IdCardTemplate;
use App\Services\IdCardTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class IdCardTemplateController
 *
 * API Controller for ID Card Template CRUD and activation operations.
 * Handles authorization and delegates logic to IdCardTemplateService.
 *
 * @tags Settings
 */
class IdCardTemplateController extends Controller
{
    public function __construct(
        private readonly IdCardTemplateService $service
    ) {}

    /**
     * List all templates.
     */
    public function index(Request $request): JsonResponse
    {
        // if (auth()->user()->denies('view id card templates')) {
        //     return response()->forbidden('Permission denied.');
        // }

        $templates = $this->service->getAllTemplates();

        return response()->success(
            IdCardTemplateResource::collection($templates),
            'Templates retrieved successfully.'
        );
    }

    /**
     * Get the active template (Used for generating PDFs).
     */
    public function active(): JsonResponse
    {
        $template = $this->service->getActiveTemplate();

        return response()->success(
            new IdCardTemplateResource($template),
            'Active template retrieved successfully.'
        );
    }

    /**
     * Create a new template.
     */
    public function store(StoreIdCardTemplateRequest $request): JsonResponse
    {
        $template = $this->service->createTemplate($request->validated());

        return response()->success(
            new IdCardTemplateResource($template),
            'Template created successfully.'
        );
    }

    /**
     * Update an existing template.
     */
    public function update(UpdateIdCardTemplateRequest $request, IdCardTemplate $id_card_template): JsonResponse
    {
        $updatedTemplate = $this->service->updateTemplate($id_card_template, $request->validated());

        return response()->success(
            new IdCardTemplateResource($updatedTemplate),
            'Template updated successfully.'
        );
    }

    /**
     * Set a template as the active one.
     */
    public function activate(IdCardTemplate $id_card_template): JsonResponse
    {
        $activeTemplate = $this->service->markAsActive($id_card_template);

        return response()->success(
            new IdCardTemplateResource($activeTemplate),
            'Template set as active successfully.'
        );
    }

    /**
     * Delete a template.
     */
    public function destroy(IdCardTemplate $id_card_template): JsonResponse
    {
        if ($id_card_template->is_active) {
            return response()->error('Cannot delete the currently active template. Please activate another template first.');
        }

        $this->service->deleteTemplate($id_card_template);

        return response()->success(null, 'Template deleted successfully.');
    }

    /**
     * Bulk Delete Templates.
     */
    public function bulkDestroy(IdCardTemplateBulkActionRequest $request): JsonResponse
    {
        $deletedCount = $this->service->bulkDeleteTemplates($request->validated('ids'));

        return response()->success(
            ['deleted_count' => $deletedCount],
            "{$deletedCount} template(s) deleted successfully. (Active templates cannot be bulk deleted)."
        );
    }
}
