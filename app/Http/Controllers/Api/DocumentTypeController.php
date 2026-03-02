<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DocumentTypes\DocumentTypeBulkActionRequest;
use App\Http\Requests\DocumentTypes\StoreDocumentTypeRequest;
use App\Http\Requests\DocumentTypes\UpdateDocumentTypeRequest;
use App\Http\Resources\DocumentTypeResource;
use App\Models\DocumentType;
use App\Services\DocumentTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class DocumentTypeController
 *
 * API Controller for Document Type CRUD and bulk operations. Handles authorization
 * via permissions and delegates logic to DocumentTypeService.
 *
 * @tags HRM Management
 */
class DocumentTypeController extends Controller
{
    /**
     * DocumentTypeController constructor.
     */
    public function __construct(
        private readonly DocumentTypeService $service
    ) {}

    /**
     * List Document Types
     *
     * Display a paginated listing of document types. Supports search and active filter.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for viewing document types.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /** @example "National ID" */
                'search' => ['nullable', 'string'],
                /** @example true */
                'is_active' => ['nullable', 'boolean'],
            ]),
            /** @default 10 */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            DocumentTypeResource::collection($items),
            'Document types retrieved successfully'
        );
    }

    /**
     * Document Type Options
     *
     * Return active document types for dropdowns.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for document type options.');
        }

        return response()->success($this->service->getOptions(), 'Document type options retrieved successfully');
    }

    /**
     * Create Document Type
     *
     * Store a newly created document type.
     */
    public function store(StoreDocumentTypeRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied for creating document type.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new DocumentTypeResource($model),
            'Document type created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Document Type
     *
     * Retrieve the details of a specific document type by its ID.
     */
    public function show(DocumentType $document_type): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for viewing document type.');
        }

        return response()->success(
            new DocumentTypeResource($document_type),
            'Document type details retrieved successfully'
        );
    }

    /**
     * Update Document Type
     *
     * Update the specified document type.
     */
    public function update(UpdateDocumentTypeRequest $request, DocumentType $document_type): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied for updating document type.');
        }

        $updated = $this->service->update($document_type, $request->validated());

        return response()->success(
            new DocumentTypeResource($updated),
            'Document type updated successfully'
        );
    }

    /**
     * Delete Document Type
     *
     * Remove the specified document type from storage.
     */
    public function destroy(DocumentType $document_type): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied for deleting document type.');
        }

        $this->service->delete($document_type);

        return response()->success(null, 'Document type deleted successfully');
    }

    /**
     * Bulk Delete Document Types
     *
     * Delete multiple document types by ID.
     */
    public function bulkDestroy(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} document types"
        );
    }

    /**
     * Bulk Activate Document Types
     *
     * Set is_active to true for the given document type IDs.
     */
    public function bulkActivate(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} document types activated"
        );
    }

    /**
     * Bulk Deactivate Document Types
     *
     * Set is_active to false for the given document type IDs.
     */
    public function bulkDeactivate(DocumentTypeBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied for bulk update.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} document types deactivated"
        );
    }
}
