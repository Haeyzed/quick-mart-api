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

class EmployeeDocumentController extends Controller
{
    public function __construct(
        private readonly EmployeeDocumentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view employee documents')) {
            return response()->forbidden('Permission denied for viewing employee documents.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                'document_type_id' => ['nullable', 'integer', 'exists:document_types,id'],
                'expired' => ['nullable', 'boolean'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            EmployeeDocumentResource::collection($items),
            'Employee documents retrieved successfully'
        );
    }

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

    public function destroy(EmployeeDocument $employee_document): JsonResponse
    {
        if (auth()->user()->denies('delete employee documents')) {
            return response()->forbidden('Permission denied for deleting employee document.');
        }

        $this->service->delete($employee_document);

        return response()->success(null, 'Employee document deleted successfully');
    }
}
