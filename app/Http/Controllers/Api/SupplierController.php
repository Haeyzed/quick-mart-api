<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Suppliers\StoreSupplierRequest;
use App\Http\Requests\Suppliers\SupplierBulkActionRequest;
use App\Http\Requests\Suppliers\SupplierClearDueRequest;
use App\Http\Requests\Suppliers\SupplierIndexRequest;
use App\Http\Requests\Suppliers\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * API Controller for Supplier CRUD and accounting operations.
 * Follows the same structure as CustomerController: permission checks in controller,
 * Store/Update requests, bulk action request, options, download, export with download/email.
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
     */
    public function index(SupplierIndexRequest $request): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for viewing suppliers list.');
        }

        $suppliers = $this->service->getSuppliers(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $suppliers->through(fn (Supplier $supplier) => new SupplierResource($supplier));

        return response()->success($suppliers, 'Suppliers retrieved successfully');
    }

    /**
     * Get supplier options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for viewing supplier options.');
        }

        return response()->success($this->service->getOptions(), 'Supplier options retrieved successfully');
    }

    /**
     * Store a newly created supplier.
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create suppliers')) {
            return response()->forbidden('Permission denied for create supplier.');
        }

        $supplier = $this->service->createSupplier($request->validated());

        return response()->success(
            new SupplierResource($supplier),
            'Supplier created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified supplier.
     */
    public function show(Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for view supplier.');
        }

        $supplier = $this->service->getSupplier($supplier);

        return response()->success(new SupplierResource($supplier), 'Supplier details retrieved successfully');
    }

    /**
     * Update the specified supplier.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('update suppliers')) {
            return response()->forbidden('Permission denied for update supplier.');
        }

        $supplier = $this->service->updateSupplier($supplier, $request->validated());

        return response()->success(new SupplierResource($supplier), 'Supplier updated successfully');
    }

    /**
     * Remove the specified supplier (soft delete via is_active).
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('delete suppliers')) {
            return response()->forbidden('Permission denied for delete supplier.');
        }

        $this->service->deleteSupplier($supplier);

        return response()->success(null, 'Supplier deleted successfully');
    }

    /**
     * Get all active suppliers (for dropdowns).
     */
    public function getAllActive(): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for viewing suppliers.');
        }

        $suppliers = $this->service->getAllActive();

        return response()->success(SupplierResource::collection($suppliers), 'Active suppliers retrieved successfully');
    }

    /**
     * Bulk delete suppliers.
     */
    public function bulkDestroy(SupplierBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete suppliers')) {
            return response()->forbidden('Permission denied for bulk delete suppliers.');
        }

        $count = $this->service->bulkDeleteSuppliers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} suppliers"
        );
    }

    /**
     * Bulk activate suppliers.
     */
    public function bulkActivate(SupplierBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update suppliers')) {
            return response()->forbidden('Permission denied for bulk update suppliers.');
        }

        $count = $this->service->bulkActivateSuppliers($request->validated()['ids']);

        return response()->success(
            ['activated_count' => $count],
            "{$count} suppliers activated"
        );
    }

    /**
     * Bulk deactivate suppliers.
     */
    public function bulkDeactivate(SupplierBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update suppliers')) {
            return response()->forbidden('Permission denied for bulk update suppliers.');
        }

        $count = $this->service->bulkDeactivateSuppliers($request->validated()['ids']);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} suppliers deactivated"
        );
    }

    /**
     * Import suppliers from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import suppliers')) {
            return response()->forbidden('Permission denied for import suppliers.');
        }

        $this->service->importSuppliers($request->file('file'));

        return response()->success(null, 'Suppliers imported successfully');
    }

    /**
     * Export suppliers to Excel or PDF (download or email).
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export suppliers')) {
            return response()->forbidden('Permission denied for export suppliers.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend(true);
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'suppliers_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Supplier Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download suppliers import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import suppliers')) {
            return response()->forbidden('Permission denied for downloading suppliers import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Get supplier ledger (purchases, payments, returns) sorted by date.
     */
    public function ledger(Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for view supplier.');
        }

        $ledger = $this->service->getLedger($supplier);

        return response()->success(['ledger' => $ledger], 'Supplier ledger retrieved successfully');
    }

    /**
     * Get total balance due for supplier.
     */
    public function balanceDue(Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for view supplier.');
        }

        $balance = $this->service->getBalanceDue($supplier);

        return response()->success(['balance_due' => $balance], 'Balance due retrieved successfully');
    }

    /**
     * Get supplier payment history.
     */
    public function payments(Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('view suppliers')) {
            return response()->forbidden('Permission denied for view supplier.');
        }

        $payments = $this->service->getPayments($supplier);

        return response()->success(['payments' => $payments], 'Supplier payments retrieved successfully');
    }

    /**
     * Record payment against supplier's due purchases.
     */
    public function clearDue(SupplierClearDueRequest $request, Supplier $supplier): JsonResponse
    {
        if (auth()->user()->denies('update suppliers')) {
            return response()->forbidden('Permission denied for update supplier.');
        }

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
