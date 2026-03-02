<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EmployeeDocuments\StoreEmployeeDocumentRequest;
use App\Http\Requests\EmployeeDocuments\UpdateEmployeeDocumentRequest;
use App\Http\Resources\EmployeeDocumentResource;
use App\Models\EmployeeDocument;
use App\Services\EmployeeDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class EmployeeDocumentController
 *
 * API Controller for Employee Document CRUD. Handles authorization via permissions
 * and delegates logic to EmployeeDocumentService.
 *
 * @tags HRM Management
 */
class EmployeeDocumentController extends Controller
{
    /**
     * EmployeeDocumentController constructor.
     */
    public function __construct(
        private readonly EmployeeDocumentService $service
    ) {}

    /**
     * List Employee Documents
     *
     * Display a paginated listing of employee documents. Supports filtering by employee, document type, and expiry.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for viewing employee documents.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /** @example 5 */
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                /** @example 1 */
                'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
                /** @example true */
                'expired' => ['nullable', 'boolean'],
            ]),
            /** @default 10 */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            EmployeeDocumentResource::collection($items),
            'Employee documents retrieved successfully'
        );
    }

    /**
     * Create Employee Document
     *
     * Store a new document for an employee (file upload handled by service).
     */
    public function store(StoreEmployeeDocumentRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create employee documents')) {
            return response()->forbidden('Permission denied for creating employee document.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new EmployeeDocumentResource($model->load(['documentType', 'employee'])),
            'Employee document created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Employee Document
     *
     * Retrieve the details of a specific employee document by its ID.
     */
    public function show(EmployeeDocument $employee_document): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for viewing employee document.');
        }

        return response()->success(
            new EmployeeDocumentResource($employee_document->load(['documentType', 'employee'])),
            'Employee document details retrieved successfully'
        );
    }

    /**
     * Update Employee Document
     *
     * Update the specified employee document; file replacement handled by service.
     */
    public function update(UpdateEmployeeDocumentRequest $request, EmployeeDocument $employee_document): JsonResponse
    {
        if (auth()->user()->denies('update employee documents')) {
            return response()->forbidden('Permission denied for updating employee document.');
        }

        $updated = $this->service->update($employee_document, $request->validated());

        return response()->success(
            new EmployeeDocumentResource($updated),
            'Employee document updated successfully'
        );
    }

    /**
     * Delete Employee Document
     *
     * Remove the specified employee document from storage (and delete file from disk).
     */
    public function destroy(EmployeeDocument $employee_document): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied for deleting employee document.');
        }

        $this->service->delete($employee_document);

        return response()->success(null, 'Employee document deleted successfully');
    }
}
