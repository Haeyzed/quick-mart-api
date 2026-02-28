<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SalaryComponents\SalaryComponentBulkActionRequest;
use App\Http\Requests\SalaryComponents\StoreSalaryComponentRequest;
use App\Http\Requests\SalaryComponents\UpdateSalaryComponentRequest;
use App\Http\Resources\SalaryComponentResource;
use App\Models\SalaryComponent;
use App\Services\SalaryComponentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SalaryComponentController extends Controller
{
    public function __construct(
        private readonly SalaryComponentService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view salary components')) {
            return response()->forbidden('Permission denied for viewing salary components.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                'search' => ['nullable', 'string'],
                'is_active' => ['nullable', 'boolean'],
                'type' => ['nullable', 'string', 'in:earning,deduction'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            SalaryComponentResource::collection($items),
            'Salary components retrieved successfully'
        );
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view salary components')) {
            return response()->forbidden('Permission denied for viewing salary component options.');
        }

        return response()->success($this->service->getOptions(), 'Salary component options retrieved successfully');
    }

    public function store(StoreSalaryComponentRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create salary components')) {
            return response()->forbidden('Permission denied for creating salary component.');
        }

        $model = $this->service->create($request->validated());

        return response()->success(
            new SalaryComponentResource($model),
            'Salary component created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    public function show(SalaryComponent $salary_component): JsonResponse
    {
        if (auth()->user()->denies('view salary components')) {
            return response()->forbidden('Permission denied for viewing salary component.');
        }

        return response()->success(
            new SalaryComponentResource($salary_component),
            'Salary component details retrieved successfully'
        );
    }

    public function update(UpdateSalaryComponentRequest $request, SalaryComponent $salary_component): JsonResponse
    {
        if (auth()->user()->denies('update salary components')) {
            return response()->forbidden('Permission denied for updating salary component.');
        }

        $updated = $this->service->update($salary_component, $request->validated());

        return response()->success(
            new SalaryComponentResource($updated),
            'Salary component updated successfully'
        );
    }

    public function destroy(SalaryComponent $salary_component): JsonResponse
    {
        if (auth()->user()->denies('delete salary components')) {
            return response()->forbidden('Permission denied for deleting salary component.');
        }

        $this->service->delete($salary_component);

        return response()->success(null, 'Salary component deleted successfully');
    }

    public function bulkDestroy(SalaryComponentBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete salary components')) {
            return response()->forbidden('Permission denied for bulk delete.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} salary components"
        );
    }
}
