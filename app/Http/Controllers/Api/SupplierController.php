<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Suppliers\SupplierBulkDestroyRequest;
use App\Http\Requests\Suppliers\SupplierBulkUpdateRequest;
use App\Http\Requests\Suppliers\SupplierClearDueRequest;
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
 * API Controller for Supplier CRUD and accounting operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, export, getAllActive, ledger, balance-due, payments, and clear-due.
 * All responses use the ResponseServiceProvider macros.
 *
 * @group Supplier Management
 */
class SupplierController extends Controller
{
    public function __construct(
        private readonly SupplierService $service
    ) {}

    /**
     * Display a paginated listing of suppliers.
     *
     * @param  SupplierIndexRequest  $request  Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated suppliers with meta and links.
     */
    /**
     * Display a paginated listing of suppliers.
     *
     * @param  SupplierIndexRequest  $request  Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated suppliers with meta and links.
     */
    public function index(SupplierIndexRequest $request): JsonResponse
    {
        $suppliers = $this->service->getSuppliers(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $suppliers->through(fn (Supplier $supplier) => new SupplierResource($supplier));

        return response()->success($suppliers, 'Suppliers fetched successfully');
    }

    /**
     * Store a newly created supplier.
     *
     * @param  SupplierRequest  $request  Validated supplier attributes.
     * @return JsonResponse Created supplier with 201 status.
     */
    /**
     * Store a newly created supplier.
     *
     * @param  SupplierRequest  $request  Validated supplier attributes.
     * @return JsonResponse Created supplier with 201 status.
     */
    public function store(SupplierRequest $request): JsonResponse
    {
        $supplier = $this->service->createSupplier($request->validated());

        return response()->success(
            new SupplierResource($supplier),
            'Supplier created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified supplier.
     *
     * @param  Supplier  $supplier  The supplier instance resolved via route model binding.
     * @return JsonResponse Supplier data.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        $supplier = $this->service->getSupplier($supplier);

        return response()->success(new SupplierResource($supplier), 'Supplier retrieved successfully');
    }

    /**
     * Update the specified supplier.
     *
     * @param  SupplierRequest  $request  Validated supplier attributes.
     * @param  Supplier  $supplier  The supplier instance to update.
     * @return JsonResponse Updated supplier.
     */
    public function update(SupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier = $this->service->updateSupplier($supplier, $request->validated());

        return response()->success(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    /**
     * Remove the specified supplier (deactivates it).
     *
     * @param  Supplier  $supplier  The supplier instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->service->deleteSupplier($supplier);

        return response()->success(null, 'Supplier deleted successfully');
    }

    /**
     * Get all active suppliers (for dropdowns).
     *
     * @return JsonResponse Collection of active suppliers.
     */
    public function getAllActive(): JsonResponse
    {
        $suppliers = $this->service->getAllActive();

        return response()->success(SupplierResource::collection($suppliers), 'Active suppliers fetched successfully');
    }

    /**
     * Bulk delete suppliers (deactivates them).
     *
     * @param  SupplierBulkDestroyRequest  $request  Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(SupplierBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteSuppliers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} suppliers"
        );
    }

    /**
     * Bulk activate suppliers by ID.
     *
     * @param  SupplierBulkUpdateRequest  $request  Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(SupplierBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateSuppliers($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} suppliers activated");
    }

    /**
     * Bulk deactivate suppliers by ID.
     *
     * @param  SupplierBulkUpdateRequest  $request  Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(SupplierBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateSuppliers($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} suppliers deactivated");
    }

    /**
     * Import suppliers from Excel/CSV file.
     *
     * @param  ImportRequest  $request  Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importSuppliers($request->file('file'));

        return response()->success(null, 'Suppliers imported successfully');
    }

    /**
     * Export suppliers to Excel or PDF.
     *
     * Supports download or email delivery based on method.
     *
     * @param  ExportRequest  $request  Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
     */
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

    /**
     * Get supplier ledger (purchases, payments, returns) sorted by date.
     *
     * @param  Supplier  $supplier  The supplier instance.
     * @return JsonResponse Ledger entries with running balance.
     */
    public function ledger(Supplier $supplier): JsonResponse
    {
        $ledger = $this->service->getLedger($supplier);

        return response()->success(['ledger' => $ledger], 'Supplier ledger fetched successfully');
    }

    /**
     * Get total balance due for supplier.
     *
     * @param  Supplier  $supplier  The supplier instance.
     * @return JsonResponse Balance due amount.
     */
    public function balanceDue(Supplier $supplier): JsonResponse
    {
        $balance = $this->service->getBalanceDue($supplier);

        return response()->success(['balance_due' => $balance], 'Balance due fetched successfully');
    }

    /**
     * Get supplier payment history.
     *
     * @param  Supplier  $supplier  The supplier instance.
     * @return JsonResponse Payment history.
     */
    public function payments(Supplier $supplier): JsonResponse
    {
        $payments = $this->service->getPayments($supplier);

        return response()->success(['payments' => $payments], 'Supplier payments fetched successfully');
    }

    /**
     * Record payment against supplier's due purchases.
     *
     * @param  SupplierClearDueRequest  $request  Validated amount, note, cash_register_id.
     * @param  Supplier  $supplier  The supplier instance.
     * @return JsonResponse Success message.
     */
    public function clearDue(SupplierClearDueRequest $request, Supplier $supplier): JsonResponse
    {
        $validated = $request->validated();
        $this->service->clearDue(
            $supplier->id,
            (float) $validated['amount'],
            $validated['note'] ?? null,
            isset($validated['cash_register_id']) ? (int) $validated['cash_register_id'] : null
        );

        return response()->success(null, 'Payment recorded successfully');
    }
}
