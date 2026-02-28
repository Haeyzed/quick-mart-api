<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalaryStructures\SalaryStructureBulkActionRequest;
use App\Http\Requests\SalaryStructures\StoreSalaryStructureRequest;
use App\Http\Requests\SalaryStructures\UpdateSalaryStructureRequest;
use App\Http\Resources\SalaryStructureResource;
use App\Models\SalaryStructure;
use App\Services\SalaryStructureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class SalaryStructureController
 *
 * API Controller for Salary Structure CRUD and bulk operations.
 *
 * @tags HRM Management
 */
class SalaryStructureController extends Controller
{
    public function __construct(
        private readonly SalaryStructureService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view salary structures')) {
            return response()->forbidden('Permission denied for viewing salary structures.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'search' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            SalaryStructureResource::collection($items),
            'Salary structures retrieved successfully'
        );
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view salary structures')) {
            return response()->forbidden('Permission denied for viewing salary structure options.');
        }

        return response()->success($this->service->getOptions(), 'Salary structure options retrieved successfully');
    }

    public function store(StoreSalaryStructureRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create salary structures')) {
            return response()->forbidden('Permission denied for creating salary structure.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new SalaryStructureResource($model->load('structureItems.salaryComponent')),
            'Salary structure created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    public function show(SalaryStructure $salary_structure): JsonResponse
    {
        if (auth()->user()->denies('view salary structures')) {
            return response()->forbidden('Permission denied for viewing salary structure.');
        }

        return response()->success(
            new SalaryStructureResource($salary_structure->load('structureItems.salaryComponent')),
            'Salary structure details retrieved successfully'
        );
    }

    public function update(UpdateSalaryStructureRequest $request, SalaryStructure $salary_structure): JsonResponse
    {
        if (auth()->user()->denies('update salary structures')) {
            return response()->forbidden('Permission denied for updating salary structure.');
        }

        $updated = $this->service->update($salary_structure, $request->validated());

        return response()->success(
            new SalaryStructureResource($updated),
            'Salary structure updated successfully'
        );
    }

    public function destroy(SalaryStructure $salary_structure): JsonResponse
    {
        if (auth()->user()->denies('delete salary structures')) {
            return response()->forbidden('Permission denied for deleting salary structure.');
        }

        $this->service->delete($salary_structure);

        return response()->success(null, 'Salary structure deleted successfully');
    }

    public function bulkDestroy(SalaryStructureBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete salary structures')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} salary structures"
        );
    }
}
