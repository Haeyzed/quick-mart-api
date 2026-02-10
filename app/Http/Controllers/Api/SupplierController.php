<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Suppliers\SupplierBulkDestroyRequest;
use App\Http\Requests\Suppliers\SupplierBulkUpdateRequest;
use App\Http\Requests\Suppliers\SupplierIndexRequest;
use App\Http\Requests\Suppliers\SupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Supplier CRUD.
 *
 * @group Supplier Management
 */
class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $service
    ) {}

    public function index(SupplierIndexRequest $request): JsonResponse
    {
        $suppliers = $this->service->getSuppliers(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $suppliers->through(fn (Supplier $supplier) => new SupplierResource($supplier));

        return response()->success($suppliers, 'Suppliers fetched successfully');
    }

    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = $this->service->createSupplier($request->validated());

        return response()->success(
            new SupplierResource($supplier),
            'Supplier created successfully',
            Response::HTTP_CREATED
        );
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $supplier = $this->service->getSupplier($supplier);

        return response()->success(new SupplierResource($supplier), 'Supplier retrieved successfully');
    }

    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier = $this->service->updateSupplier($supplier, $request->validated());

        return response()->success(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->service->deleteSupplier($supplier);

        return response()->success(null, 'Supplier deleted successfully');
    }

    public function getAllActive(): JsonResponse
    {
        $suppliers = $this->service->getAllActive();

        return response()->success(SupplierResource::collection($suppliers), 'Active suppliers fetched successfully');
    }

    public function bulkDestroy(SupplierBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteSuppliers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} suppliers"
        );
    }

    public function bulkActivate(SupplierBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateSuppliers($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} suppliers activated");
    }

    public function bulkDeactivate(SupplierBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateSuppliers($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} suppliers deactivated");
    }

    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importSuppliers($request->file('file'));

        return response()->success(null, 'Suppliers imported successfully');
    }

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();
        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportSuppliers(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(Storage::disk('public')->path($filePath));
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}
