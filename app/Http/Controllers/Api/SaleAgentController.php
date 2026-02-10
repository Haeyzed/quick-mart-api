<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaleAgents\SaleAgentBulkDestroyRequest;
use App\Http\Requests\SaleAgents\SaleAgentIndexRequest;
use App\Http\Requests\SaleAgents\SaleAgentRequest;
use App\Http\Resources\SaleAgentResource;
use App\Models\Employee;
use App\Services\SaleAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * API Controller for Sale Agent (Employee with is_sale_agent) CRUD.
 *
 * @group Sale Agent Management
 */
class SaleAgentController extends Controller
{
    public function __construct(
        private readonly SaleAgentService $service
    ) {}

    public function index(SaleAgentIndexRequest $request): JsonResponse
    {
        $saleAgents = $this->service->getSaleAgents(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $saleAgents->through(fn (Employee $employee) => new SaleAgentResource($employee));

        return response()->success($saleAgents, 'Sale agents fetched successfully');
    }

    public function store(SaleAgentRequest $request): JsonResponse
    {
        $saleAgent = $this->service->createSaleAgent($request->validated());

        return response()->success(
            new SaleAgentResource($saleAgent->load(['department', 'designation', 'shift', 'user'])),
            'Sale agent created successfully',
            Response::HTTP_CREATED
        );
    }

    public function show(Employee $sale_agent): JsonResponse
    {
        $saleAgent = $this->service->getSaleAgent($sale_agent);

        return response()->success(new SaleAgentResource($saleAgent), 'Sale agent retrieved successfully');
    }

    public function update(SaleAgentRequest $request, Employee $sale_agent): JsonResponse
    {
        $saleAgent = $this->service->updateSaleAgent($sale_agent, $request->validated());

        return response()->success(new SaleAgentResource($saleAgent), 'Sale agent updated successfully');
    }

    public function destroy(Employee $sale_agent): JsonResponse
    {
        $this->service->deleteSaleAgent($sale_agent);

        return response()->success(null, 'Sale agent deleted successfully');
    }

    public function getAllActive(): JsonResponse
    {
        $saleAgents = $this->service->getAllActive();

        return response()->success(
            SaleAgentResource::collection($saleAgents),
            'Active sale agents fetched successfully'
        );
    }

    public function bulkDestroy(SaleAgentBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteSaleAgents($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} sale agents"
        );
    }
}
