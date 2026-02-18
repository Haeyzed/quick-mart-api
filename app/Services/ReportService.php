<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\AuditsExport;
use App\Exports\CustomerDueReportExport;
use App\Exports\ReportTableExport;
use App\Mail\ExportMail;
use App\Models\Challan;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\GeneralSetting;
use App\Models\Income;
use App\Models\MailSetting;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductSale;
use App\Models\ProductWarehouse;
use App\Models\Purchase;
use App\Models\ReturnPurchase;
use App\Models\Returns;
use App\Models\Sale;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use OwenIt\Auditing\Models\Audit;
use RuntimeException;

/**
 * Report service for due reports, sales, purchases, profit/loss, and other analytics.
 *
 * Handles customer due, supplier due, customer/customer-group reports, warehouse stock,
 * daily/monthly sale and purchase, best seller, sale report chart, profit/loss,
 * product reports, and audit log export.
 *
 * Performance notes:
 * - Due reports batch-load returns/return-purchases to avoid N+1
 * - Daily/monthly reports use single aggregated queries with GROUP BY
 * - Customer reports use eager loading for productSales.product
 * - Warehouse stock uses join instead of whereHas for filtering
 */
class ReportService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Retrieve customer due report with optional filters and pagination.
     *
     * Sales with payment_status != 'paid', optionally filtered by date and customer.
     * Each row includes grand_total, returned_amount, paid, and due.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<array<string, mixed>>
     */
    public function getCustomerDueReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('due-report');

        $query = Sale::query()
            ->with('customer:id,name,phone_number')
            ->whereNull('deleted_at')
            ->where('payment_status', '!=', 'paid')
            ->when(!empty($filters['start_date'] ?? null), fn($q) => $q->whereDate('created_at', '>=', $filters['start_date']))
            ->when(!empty($filters['end_date'] ?? null), fn($q) => $q->whereDate('created_at', '<=', $filters['end_date']))
            ->when(!empty($filters['customer_id'] ?? null), fn($q) => $q->where('customer_id', $filters['customer_id']))
            ->orderByDesc('created_at');

        $sales = $query->paginate($perPage);
        $saleIds = $sales->getCollection()->pluck('id')->all();

        $returnsBySale = [];
        if (!empty($saleIds)) {
            Returns::query()
                ->whereIn('sale_id', $saleIds)
                ->get()
                ->each(function (Returns $r) use (&$returnsBySale): void {
                    $amount = $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total;
                    $returnsBySale[$r->sale_id] = ($returnsBySale[$r->sale_id] ?? 0) + $amount;
                });
        }

        $rows = $sales->getCollection()->map(function (Sale $sale) use ($returnsBySale): array {
            $returnedAmount = (float)($returnsBySale[$sale->id] ?? 0);
            $exchangeRate = $sale->exchange_rate ?: 1;
            $grandTotal = (float)$sale->grand_total / $exchangeRate;
            $paid = (float)($sale->paid_amount ?? 0) / $exchangeRate;
            $due = max(0, $grandTotal - $returnedAmount - $paid);

            return [
                'id' => $sale->id,
                'date' => $sale->created_at?->format('Y-m-d'),
                'reference_no' => $sale->reference_no,
                'customer_name' => $sale->customer?->name ?? '—',
                'customer_phone' => $sale->customer?->phone_number ?? '—',
                'grand_total' => round($grandTotal, 2),
                'returned_amount' => round($returnedAmount, 2),
                'paid' => round($paid, 2),
                'due' => round($due, 2),
            ];
        });

        $sales->setCollection($rows);

        return $sales;
    }

    /**
     * Export customer due report to Excel or PDF.
     *
     * @param array<string, mixed> $filters
     * @param array<string> $columns
     * @return string Relative storage path of the generated file.
     */
    public function exportCustomerDueReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('due-report');

        $sales = Sale::query()
            ->with('customer:id,name,phone_number')
            ->whereNull('deleted_at')
            ->where('payment_status', '!=', 'paid')
            ->when(!empty($filters['start_date'] ?? null), fn($q) => $q->whereDate('created_at', '>=', $filters['start_date']))
            ->when(!empty($filters['end_date'] ?? null), fn($q) => $q->whereDate('created_at', '<=', $filters['end_date']))
            ->when(!empty($filters['customer_id'] ?? null), fn($q) => $q->where('customer_id', $filters['customer_id']))
            ->orderByDesc('created_at')
            ->get();

        $returnsBySale = Returns::query()
            ->whereIn('sale_id', $sales->pluck('id'))
            ->get()
            ->groupBy('sale_id')
            ->map(fn($returns) => $returns->sum(fn(Returns $r) => $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total));

        $rows = $sales->map(function (Sale $sale) use ($returnsBySale): array {
            $returnedAmount = (float)($returnsBySale[$sale->id] ?? 0);
            $exchangeRate = $sale->exchange_rate ?: 1;
            $grandTotal = (float)$sale->grand_total / $exchangeRate;
            $paid = (float)($sale->paid_amount ?? 0) / $exchangeRate;
            $due = max(0, $grandTotal - $returnedAmount - $paid);

            return [
                'id' => $sale->id,
                'date' => $sale->created_at?->format('Y-m-d'),
                'reference_no' => $sale->reference_no,
                'customer_name' => $sale->customer?->name ?? '—',
                'customer_phone' => $sale->customer?->phone_number ?? '—',
                'grand_total' => round($grandTotal, 2),
                'returned_amount' => round($returnedAmount, 2),
                'paid' => round($paid, 2),
                'due' => round($due, 2),
            ];
        });

        $fileName = 'customer_due_report_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new CustomerDueReportExport($rows, $columns), $relativePath, 'public');
        } else {
            $pdf = PDF::loadView('exports.customer-due-report-pdf', compact('rows', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName, 'Customer Due Report');
        }

        return $relativePath;
    }

    private function sendExportEmail(User $user, string $path, string $fileName, string $title = 'Customer Due Report'): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn() => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();
        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, $title, $generalSetting)
        );
    }

    /**
     * Retrieve product quantity alert report.
     *
     * Products where qty is below alert_quantity (is_active only).
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Model>
     */
    public function getProductQtyAlert(array $filters = [], int $perPage = 10)
    {
        $this->requirePermission('product-qty-alert');

        return Product::query()
            ->where('is_active', true)
            ->whereColumn('alert_quantity', '>', 'qty')
            ->whereNotNull('alert_quantity')
            ->orderBy('qty')
            ->paginate($perPage);
    }

    /**
     * Retrieve product expiry report.
     *
     * Product batches with qty > 0, optionally filtered by expiry date.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<ProductBatch>
     */
    public function getProductExpiry(array $filters = [], int $perPage = 10)
    {
        $this->requirePermission('product-expiry-report');

        return ProductBatch::query()
            ->with('product:id,name,code,image')
            ->where('product_batches.qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->when(!empty($filters['expired_before'] ?? null), fn($q) => $q->whereDate('expired_date', '<=', $filters['expired_before']))
            ->orderBy('expired_date')
            ->paginate($perPage);
    }

    /**
     * Retrieve warehouse stock report with optional warehouse filter.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getWarehouseStock(array $filters = []): array
    {
        $this->requirePermission('warehouse-stock-report');

        $warehouseId = $filters['warehouse_id'] ?? null;

        $baseQuery = ProductWarehouse::query()
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->when($warehouseId, fn($q) => $q->where('product_warehouse.warehouse_id', $warehouseId));

        $totalItem = (clone $baseQuery)->where('product_warehouse.qty', '>', 0)->count();
        $totalQty = (float)((clone $baseQuery)->sum('product_warehouse.qty'));

        if ($warehouseId) {
            $totalPrice = (float)((clone $baseQuery)->selectRaw('SUM(COALESCE(products.price, 0) * product_warehouse.qty) as total')->value('total') ?? 0);
            $totalCost = (float)((clone $baseQuery)->selectRaw('SUM(COALESCE(products.cost, 0) * product_warehouse.qty) as total')->value('total') ?? 0);
        } else {
            $totalPrice = (float)(Product::query()->where('is_active', true)->selectRaw('SUM(COALESCE(price, 0) * qty) as total')->value('total') ?? 0);
            $totalCost = (float)(Product::query()->where('is_active', true)->selectRaw('SUM(COALESCE(cost, 0) * qty) as total')->value('total') ?? 0);
        }

        $itemsQuery = ProductWarehouse::query()
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->with(['product:id,name,code,image,cost,price', 'warehouse:id,name'])
            ->select('product_warehouse.*')
            ->orderBy('product_warehouse.id');

        $perPage = (int)($filters['per_page'] ?? 15);
        $items = $itemsQuery->paginate($perPage);

        return [
            'summary' => [
                'total_item' => $totalItem,
                'total_qty' => round($totalQty, 2),
                'total_price' => round($totalPrice, 2),
                'total_cost' => round($totalCost, 2),
            ],
            'items' => $items,
        ];
    }

    /**
     * Retrieve sale report - paginated sales in date range.
     */
    public function getSaleReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('sale-report');

        return Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['warehouse_id'] ?? null), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve purchase report - paginated purchases in date range.
     */
    public function getPurchaseReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('purchase-report');

        return Purchase::query()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['warehouse_id'] ?? null), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve payment report - paginated payments in date range.
     */
    public function getPaymentReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('payment-report');

        $query = Payment::query()
            ->with(['sale:id,reference_no', 'purchase:id,reference_no', 'user:id,name'])
            ->whereDate('payment_at', '>=', $filters['start_date'])
            ->whereDate('payment_at', '<=', $filters['end_date']);

        $type = $filters['type'] ?? 'all';
        if ($type === 'sale') {
            $query->whereNotNull('sale_id');
        } elseif ($type === 'purchase') {
            $query->whereNotNull('purchase_id');
        }

        return $query->orderByDesc('payment_at')->paginate($perPage);
    }

    /**
     * Retrieve supplier due report - purchases with outstanding balance.
     */
    public function getSupplierDueReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('supplier-due-report');

        $query = Purchase::query()
            ->with('supplier:id,name,phone_number')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['supplier_id'] ?? null), fn($q) => $q->where('supplier_id', $filters['supplier_id']))
            ->orderByDesc('created_at');

        $purchases = $query->paginate($perPage);
        $purchaseIds = $purchases->getCollection()->pluck('id')->all();

        $returnsByPurchase = [];
        if (!empty($purchaseIds)) {
            ReturnPurchase::query()
                ->whereIn('purchase_id', $purchaseIds)
                ->get()
                ->each(function (ReturnPurchase $r) use (&$returnsByPurchase): void {
                    $amount = $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total;
                    $returnsByPurchase[$r->purchase_id] = ($returnsByPurchase[$r->purchase_id] ?? 0) + $amount;
                });
        }

        $rows = $purchases->getCollection()->map(function (Purchase $purchase) use ($returnsByPurchase): array {
            $returnedAmount = (float)($returnsByPurchase[$purchase->id] ?? 0);
            $exchangeRate = $purchase->exchange_rate ?: 1;
            $grandTotal = (float)$purchase->grand_total / $exchangeRate;
            $paid = (float)($purchase->paid_amount ?? 0) / $exchangeRate;
            $due = max(0, $grandTotal - $returnedAmount - $paid);

            return [
                'id' => $purchase->id,
                'date' => $purchase->created_at?->format('Y-m-d'),
                'reference_no' => $purchase->reference_no,
                'supplier_name' => $purchase->supplier?->name ?? '—',
                'supplier_phone' => $purchase->supplier?->phone_number ?? '—',
                'grand_total' => round($grandTotal, 2),
                'returned_amount' => round($returnedAmount, 2),
                'paid' => round($paid, 2),
                'due' => round($due, 2),
            ];
        });

        $purchases->setCollection($rows);

        return $purchases;
    }

    /**
     * Retrieve challan report - closed challans in date range.
     */
    public function getChallanReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('packing_slip_challan');

        $basedOn = $filters['based_on'] ?? 'created_at';

        return Challan::query()
            ->with('courier:id,name')
            ->closed()
            ->whereDate($basedOn, '>=', $filters['start_date'])
            ->whereDate($basedOn, '<=', $filters['end_date'])
            ->orderByDesc($basedOn)
            ->paginate($perPage);
    }

    /**
     * Retrieve product report - products with stock, optionally filtered by warehouse and category.
     *
     * @param array<string, mixed> $filters
     */
    public function getProductReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('product-report');

        $warehouseId = $filters['warehouse_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $query = ProductWarehouse::query()
            ->with(['product:id,name,code,image,cost,price,category_id', 'product.category:id,name', 'warehouse:id,name'])
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->select('product_warehouse.*')
            ->orderBy('products.name');

        return $query->paginate($perPage);
    }

    /**
     * Retrieve customer report - sales by customer in date range.
     *
     * Includes product string and total_cost (cost of goods) per sale for consistency with legacy reports.
     *
     * @param array<string, mixed> $filters
     */
    public function getCustomerReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customer-report');

        $sales = Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name', 'productSales.product:id,name,code,cost'])
            ->whereNull('deleted_at')
            ->where('customer_id', $filters['customer_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $rows = $sales->getCollection()->map(fn(Sale $sale) => $this->mapSaleToCustomerReportRow($sale));

        $sales->setCollection($rows);

        return $sales;
    }

    /**
     * Map a Sale model to a customer/customer-group report row array.
     *
     * Requires productSales and productSales.product to be eager loaded.
     *
     * @return array<string, mixed>
     */
    private function mapSaleToCustomerReportRow(Sale $sale): array
    {
        $productParts = [];
        $totalCost = 0.0;
        foreach ($sale->productSales as $ps) {
            $netQty = max(0, (float)$ps->qty - (float)($ps->return_qty ?? 0));
            $cost = $ps->product ? (float)$ps->product->cost : 0;
            $totalCost += $cost * $netQty;
            $productParts[] = ($ps->product ? $ps->product->name : '—') . ' (' . number_format($netQty, 2) . ')';
        }
        $exchangeRate = $sale->exchange_rate ?: 1;
        $grandTotal = (float)$sale->grand_total / $exchangeRate;
        $paid = (float)($sale->paid_amount ?? 0) / $exchangeRate;
        $due = max(0, $grandTotal - $paid);

        return [
            'id' => $sale->id,
            'date' => $sale->created_at?->format('Y-m-d'),
            'reference_no' => $sale->reference_no,
            'warehouse' => $sale->warehouse?->name ?? '—',
            'customer' => $sale->customer?->name ?? '—',
            'product' => implode(', ', $productParts),
            'total_cost' => round($totalCost, 2),
            'grand_total' => round($grandTotal, 2),
            'paid' => round($paid, 2),
            'due' => round($due, 2),
            'status' => $sale->sale_status ?? null,
        ];
    }

    /**
     * Retrieve customer group report - sales by customers in the given group.
     *
     * Includes product string and total_cost per sale for consistency with legacy reports.
     *
     * @param array<string, mixed> $filters
     */
    public function getCustomerGroupReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customer-group-report');

        $customerIds = Customer::query()
            ->where('customer_group_id', $filters['customer_group_id'])
            ->pluck('id');

        $sales = Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name', 'productSales.product:id,name,code,cost'])
            ->whereNull('deleted_at')
            ->whereIn('customer_id', $customerIds)
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $rows = $sales->getCollection()->map(fn(Sale $sale) => $this->mapSaleToCustomerReportRow($sale));

        $sales->setCollection($rows);

        return $sales;
    }

    /**
     * Retrieve supplier report - purchases by supplier in date range.
     *
     * @param array<string, mixed> $filters
     */
    public function getSupplierReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('supplier-report');

        return Purchase::query()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->where('supplier_id', $filters['supplier_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve user report - sales by user in date range.
     *
     * @param array<string, mixed> $filters
     */
    public function getUserReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('user-report');

        return Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name', 'user:id,name'])
            ->whereNull('deleted_at')
            ->where('user_id', $filters['user_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve biller report - sales by biller in date range.
     *
     * @param array<string, mixed> $filters
     */
    public function getBillerReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('biller-report');

        return Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->whereNull('deleted_at')
            ->where('biller_id', $filters['biller_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve warehouse report - sales and/or purchases by warehouse.
     *
     * @param array<string, mixed> $filters
     */
    public function getWarehouseReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('warehouse-report');

        $warehouseId = (int)$filters['warehouse_id'];
        $type = $filters['type'] ?? 'both';

        if ($type === 'sales') {
            return Sale::query()
                ->with(['customer:id,name', 'warehouse:id,name'])
                ->whereNull('deleted_at')
                ->where('warehouse_id', $warehouseId)
                ->whereDate('created_at', '>=', $filters['start_date'])
                ->whereDate('created_at', '<=', $filters['end_date'])
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }

        if ($type === 'purchases') {
            return Purchase::query()
                ->with(['supplier:id,name', 'warehouse:id,name'])
                ->where('warehouse_id', $warehouseId)
                ->whereDate('created_at', '>=', $filters['start_date'])
                ->whereDate('created_at', '<=', $filters['end_date'])
                ->orderByDesc('created_at')
                ->paginate($perPage);
        }

        return Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->whereNull('deleted_at')
            ->where('warehouse_id', $warehouseId)
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve daily sale objective report - DSO alerts from dso_alerts table.
     *
     * @param array<string, mixed> $filters
     */
    public function getDailySaleObjective(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('dso-report');

        $queryStart = date('Y-m-d', strtotime('+1 day', strtotime($filters['start_date'])));
        $queryEnd = date('Y-m-d', strtotime('+1 day', strtotime($filters['end_date'])));

        $query = DB::table('dso_alerts')
            ->whereDate('created_at', '>=', $queryStart)
            ->whereDate('created_at', '<=', $queryEnd)
            ->orderByDesc('created_at');

        $total = $query->count();
        $page = (int)($filters['page'] ?? 1);
        $perPage = (int)($filters['per_page'] ?? 10);
        $offset = ($page - 1) * $perPage;

        $rows = $query->offset($offset)->limit($perPage)->get();

        $data = $rows->map(function ($row, $key) {
            $decoded = json_decode($row->product_info);
            $productList = '';
            if (is_array($decoded)) {
                $parts = array_map(function ($p) {
                    $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                    $code = is_object($p) ? ($p->code ?? '') : ($p['code'] ?? '');

                    return $name . ' [' . $code . ']';
                }, $decoded);
                $productList = implode(', ', $parts);
            } elseif (is_object($decoded) && (isset($decoded->name) || isset($decoded->code))) {
                $productList = ($decoded->name ?? '') . ' [' . ($decoded->code ?? '') . ']';
            }

            return [
                'id' => $row->id,
                'key' => $key,
                'date' => date('Y-m-d', strtotime('-1 day', strtotime($row->created_at))),
                'product_info' => $productList,
                'number_of_products' => (int)$row->number_of_products,
                'created_at' => $row->created_at,
            ];
        });

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $data,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Retrieve audits with optional filters and pagination.
     *
     * Role-based: staff users only see their own audits.
     * Requires audit-logs-index permission.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Audit>
     */
    public function getAudits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('audit-logs-index');

        $user = Auth::user();

        $query = Audit::query()
            ->with('user')
            ->when($user && $user->isStaff(), fn($q) => $q->where('audits.user_id', $user->id))
            ->when(!empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn($subQ) => $subQ
                    ->where('audits.event', 'like', $term)
                    ->orWhere('audits.auditable_type', 'like', $term)
                    ->orWhere('audits.auditable_id', 'like', $term)
                    ->orWhere('audits.tags', 'like', $term)
                    ->orWhereHas('user', fn($u) => $u->where('name', 'like', $term))
                );
            })
            ->when(!empty($filters['event'] ?? null), fn($q) => $q->where('audits.event', $filters['event']))
            ->when(!empty($filters['auditable_type'] ?? null), fn($q) => $q->where('audits.auditable_type', 'like', "%{$filters['auditable_type']}%"))
            ->when(!empty($filters['ip_address'] ?? null), fn($q) => $q->where('audits.ip_address', 'like', "%{$filters['ip_address']}%"))
            ->when(!empty($filters['user'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['user']}%";
                $q->whereHas('user', fn($u) => $u->where('name', 'like', $term));
            })
            ->when(!empty($filters['date_from'] ?? null), fn($q) => $q->whereDate('audits.created_at', '>=', $filters['date_from']))
            ->when(!empty($filters['date_to'] ?? null), fn($q) => $q->whereDate('audits.created_at', '<=', $filters['date_to']))
            ->orderByDesc('audits.id');

        return $query->paginate($perPage);
    }

    /**
     * Export audits to Excel or PDF.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @return string Relative storage path of the generated file.
     */
    public function exportAudits(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('audit-logs-export');

        $fileName = 'audits_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new AuditsExport($ids, $columns), $relativePath, 'public');
        } else {
            $audits = Audit::query()
                ->with('user')
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderByDesc('id')
                ->get();
            $pdf = PDF::loadView('exports.audits-pdf', compact('audits', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName, 'Audit Log');
        }

        return $relativePath;
    }

    public function exportProductQtyAlert(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('product-qty-alert');

        $products = Product::query()
            ->with('saleUnit:id,name')
            ->where('is_active', true)
            ->whereColumn('alert_quantity', '>', 'qty')
            ->whereNotNull('alert_quantity')
            ->orderBy('qty')
            ->get();

        $columnLabels = [
            'name' => 'Product Name',
            'code' => 'Code',
            'qty' => 'Quantity',
            'alert_quantity' => 'Alert Quantity',
            'unit' => 'Unit',
        ];

        $rows = $products->map(fn(Product $p) => [
            'name' => $p->name,
            'code' => $p->code ?? '—',
            'qty' => (float)$p->qty,
            'alert_quantity' => (float)($p->alert_quantity ?? 0),
            'unit' => $p->saleUnit?->name ?? '—',
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'product_qty_alert_' . now()->timestamp,
            'Product Quantity Alert Report',
            $format,
            $user,
            $method,
            ['qty', 'alert_quantity']
        );
    }

    /**
     * Generic report table export to Excel or PDF.
     *
     * @param Collection<int, array<string, mixed>> $rows
     * @param array<string, string> $columnLabels
     * @param array<string> $columns
     */
    private function exportReportTable(
        Collection $rows,
        array      $columnLabels,
        array      $columns,
        string     $fileName,
        string     $reportTitle,
        string     $format,
        ?User      $user,
        string     $method,
        ?array     $rightAlignColumns = null
    ): string
    {
        $ext = $format === 'pdf' ? 'pdf' : 'xlsx';
        $relativePath = 'exports/' . $fileName . '.' . $ext;

        $cols = !empty($columns) ? $columns : array_keys($columnLabels);
        if ($format === 'excel') {
            Excel::store(new ReportTableExport($rows, $columnLabels, $cols), $relativePath, 'public');
        } else {
            $pdf = PDF::loadView('exports.report-table-pdf', [
                'title' => $reportTitle,
                'rows' => $rows->all(),
                'columns' => $cols,
                'columnLabels' => $columnLabels,
                'rightAlignColumns' => $rightAlignColumns ?? [],
            ]);
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName . '.' . $ext, $reportTitle);
        }

        return $relativePath;
    }

    public function exportProductExpiry(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('product-expiry-report');

        $batches = ProductBatch::query()
            ->with('product:id,name,code')
            ->where('product_batches.qty', '>', 0)
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->when(!empty($filters['expired_before'] ?? null), fn($q) => $q->whereDate('expired_date', '<=', $filters['expired_before']))
            ->orderBy('expired_date')
            ->get();

        $columnLabels = [
            'product_name' => 'Product Name',
            'product_code' => 'Product Code',
            'batch_no' => 'Batch No',
            'qty' => 'Quantity',
            'expired_date' => 'Expiry Date',
        ];

        $rows = $batches->map(fn(ProductBatch $b) => [
            'product_name' => $b->product?->name ?? '—',
            'product_code' => $b->product?->code ?? '—',
            'batch_no' => $b->batch_no ?? '—',
            'qty' => (float)$b->qty,
            'expired_date' => $b->expired_date?->format('Y-m-d') ?? '—',
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'product_expiry_' . now()->timestamp,
            'Product Expiry Report',
            $format,
            $user,
            $method,
            ['qty']
        );
    }

    public function exportWarehouseStock(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('warehouse-stock-report');

        $warehouseId = $filters['warehouse_id'] ?? null;
        $itemsQuery = ProductWarehouse::query()
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->with(['product:id,name,code', 'warehouse:id,name'])
            ->select('product_warehouse.*')
            ->orderBy('product_warehouse.id');

        $items = $itemsQuery->get();

        $columnLabels = [
            'product_name' => 'Product Name',
            'product_code' => 'Product Code',
            'warehouse' => 'Warehouse',
            'qty' => 'Quantity',
            'price' => 'Price',
        ];

        $rows = $items->map(fn($item) => [
            'product_name' => $item->product?->name ?? '—',
            'product_code' => $item->product?->code ?? '—',
            'warehouse' => $item->warehouse?->name ?? '—',
            'qty' => (float)$item->qty,
            'price' => round((float)($item->price ?? 0), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'warehouse_stock_' . now()->timestamp,
            'Warehouse Stock Report',
            $format,
            $user,
            $method,
            ['qty', 'price']
        );
    }

    public function exportDailySale(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('daily-sale');

        $report = $this->getDailySale($filters);
        $rows = collect($report['data']);
        $columnLabels = [
            'day' => 'Day',
            'date' => 'Date',
            'total_discount' => 'Total Discount',
            'order_discount' => 'Order Discount',
            'total_tax' => 'Total Tax',
            'order_tax' => 'Order Tax',
            'shipping_cost' => 'Shipping Cost',
            'grand_total' => 'Grand Total',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'daily_sale_' . now()->timestamp,
            'Daily Sale Report ' . $report['year'] . '-' . str_pad((string)$report['month'], 2, '0', STR_PAD_LEFT),
            $format,
            $user,
            $method,
            ['total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total']
        );
    }

    /**
     * Retrieve daily sale report for a given month.
     *
     * Uses a single aggregated query with GROUP BY to avoid N queries per day.
     *
     * @param array<string, mixed> $filters
     * @return array{year: int, month: int, days_in_month: int, data: array<int, array<string, mixed>>}
     */
    public function getDailySale(array $filters = []): array
    {
        $this->requirePermission('daily-sale');

        $year = (int)$filters['year'];
        $month = (int)$filters['month'];
        $warehouseId = $filters['warehouse_id'] ?? null;
        $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));

        $query = Sale::query()
            ->whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('
                DAY(created_at) as day,
                DATE(created_at) as date,
                COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
            ')
            ->groupByRaw('DAY(created_at), DATE(created_at)');

        $rowsByDay = $query->get()->keyBy('day');

        $data = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $row = $rowsByDay->get($day);
            $data[] = [
                'day' => $day,
                'date' => $date,
                'total_discount' => round((float)($row->total_discount ?? 0), 2),
                'order_discount' => round((float)($row->order_discount ?? 0), 2),
                'total_tax' => round((float)($row->total_tax ?? 0), 2),
                'order_tax' => round((float)($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float)($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float)($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'data' => $data,
        ];
    }

    public function exportMonthlySale(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('monthly-sale');

        $report = $this->getMonthlySale($filters);
        $rows = collect($report['data']);
        $columnLabels = [
            'month' => 'Month',
            'month_name' => 'Month Name',
            'total_discount' => 'Total Discount',
            'order_discount' => 'Order Discount',
            'total_tax' => 'Total Tax',
            'order_tax' => 'Order Tax',
            'shipping_cost' => 'Shipping Cost',
            'grand_total' => 'Grand Total',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'monthly_sale_' . now()->timestamp,
            'Monthly Sale Report ' . $report['year'],
            $format,
            $user,
            $method,
            ['total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total']
        );
    }

    /**
     * Retrieve monthly sale report for a given year.
     *
     * Uses a single aggregated query with GROUP BY to avoid 12 separate queries.
     *
     * @param array<string, mixed> $filters
     * @return array{year: int, data: array<int, array<string, mixed>>}
     */
    public function getMonthlySale(array $filters = []): array
    {
        $this->requirePermission('monthly-sale');

        $year = (int)$filters['year'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $query = Sale::query()
            ->whereNull('deleted_at')
            ->whereYear('created_at', $year)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('
                MONTH(created_at) as month,
                COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
            ')
            ->groupByRaw('MONTH(created_at)');

        $rowsByMonth = $query->get()->keyBy('month');

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $rowsByMonth->get($m);
            $data[] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'total_discount' => round((float)($row->total_discount ?? 0), 2),
                'order_discount' => round((float)($row->order_discount ?? 0), 2),
                'total_tax' => round((float)($row->total_tax ?? 0), 2),
                'order_tax' => round((float)($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float)($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float)($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'data' => $data,
        ];
    }

    public function exportDailyPurchase(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('daily-purchase');

        $report = $this->getDailyPurchase($filters);
        $rows = collect($report['data']);
        $columnLabels = [
            'day' => 'Day',
            'date' => 'Date',
            'total_discount' => 'Total Discount',
            'order_discount' => 'Order Discount',
            'total_tax' => 'Total Tax',
            'order_tax' => 'Order Tax',
            'shipping_cost' => 'Shipping Cost',
            'grand_total' => 'Grand Total',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'daily_purchase_' . now()->timestamp,
            'Daily Purchase Report ' . $report['year'] . '-' . str_pad((string)$report['month'], 2, '0', STR_PAD_LEFT),
            $format,
            $user,
            $method,
            ['total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total']
        );
    }

    /**
     * Retrieve daily purchase report for a given month.
     *
     * Uses a single aggregated query with GROUP BY to avoid N queries per day.
     *
     * @param array<string, mixed> $filters
     * @return array{year: int, month: int, days_in_month: int, data: array<int, array<string, mixed>>}
     */
    public function getDailyPurchase(array $filters = []): array
    {
        $this->requirePermission('daily-purchase');

        $year = (int)$filters['year'];
        $month = (int)$filters['month'];
        $warehouseId = $filters['warehouse_id'] ?? null;
        $daysInMonth = (int)date('t', mktime(0, 0, 0, $month, 1, $year));

        $query = Purchase::query()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('
                DAY(created_at) as day,
                DATE(created_at) as date,
                COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
            ')
            ->groupByRaw('DAY(created_at), DATE(created_at)');

        $rowsByDay = $query->get()->keyBy('day');

        $data = [];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $row = $rowsByDay->get($day);
            $data[] = [
                'day' => $day,
                'date' => $date,
                'total_discount' => round((float)($row->total_discount ?? 0), 2),
                'order_discount' => round((float)($row->order_discount ?? 0), 2),
                'total_tax' => round((float)($row->total_tax ?? 0), 2),
                'order_tax' => round((float)($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float)($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float)($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'data' => $data,
        ];
    }

    public function exportMonthlyPurchase(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('monthly-purchase');

        $report = $this->getMonthlyPurchase($filters);
        $rows = collect($report['data']);
        $columnLabels = [
            'month' => 'Month',
            'month_name' => 'Month Name',
            'total_discount' => 'Total Discount',
            'order_discount' => 'Order Discount',
            'total_tax' => 'Total Tax',
            'order_tax' => 'Order Tax',
            'shipping_cost' => 'Shipping Cost',
            'grand_total' => 'Grand Total',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'monthly_purchase_' . now()->timestamp,
            'Monthly Purchase Report ' . $report['year'],
            $format,
            $user,
            $method,
            ['total_discount', 'order_discount', 'total_tax', 'order_tax', 'shipping_cost', 'grand_total']
        );
    }

    /**
     * Retrieve monthly purchase report for a given year.
     *
     * Uses a single aggregated query with GROUP BY to avoid 12 separate queries.
     *
     * @param array<string, mixed> $filters
     * @return array{year: int, data: array<int, array<string, mixed>>}
     */
    public function getMonthlyPurchase(array $filters = []): array
    {
        $this->requirePermission('monthly-purchase');

        $year = (int)$filters['year'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $query = Purchase::query()
            ->whereYear('created_at', $year)
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->selectRaw('
                MONTH(created_at) as month,
                COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
            ')
            ->groupByRaw('MONTH(created_at)');

        $rowsByMonth = $query->get()->keyBy('month');

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $row = $rowsByMonth->get($m);
            $data[] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'total_discount' => round((float)($row->total_discount ?? 0), 2),
                'order_discount' => round((float)($row->order_discount ?? 0), 2),
                'total_tax' => round((float)($row->total_tax ?? 0), 2),
                'order_tax' => round((float)($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float)($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float)($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'data' => $data,
        ];
    }

    public function exportBestSeller(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('best-seller');

        $report = $this->getBestSeller($filters);
        $rows = collect($report['data']);
        $columnLabels = [
            'month' => 'Month',
            'month_name' => 'Month Name',
            'product_id' => 'Product ID',
            'product_name' => 'Product Name',
            'sold_qty' => 'Sold Qty',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'best_seller_' . now()->timestamp,
            'Best Seller Report',
            $format,
            $user,
            $method,
            ['sold_qty']
        );
    }

    /**
     * Retrieve best seller report - top selling product per month.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getBestSeller(array $filters = []): array
    {
        $this->requirePermission('best-seller');

        $months = (int)($filters['months'] ?? 3);
        $warehouseId = $filters['warehouse_id'] ?? null;

        $endDate = now()->endOfMonth();
        $startDate = now()->subMonths($months - 1)->startOfMonth();

        $data = [];
        $current = $startDate->copy();

        while ($current->lte($endDate)) {
            $monthStart = $current->format('Y-m-01');
            $monthEnd = $current->format('Y-m-t');

            $query = ProductSale::query()
                ->join('sales', 'product_sales.sale_id', '=', 'sales.id')
                ->join('products', 'product_sales.product_id', '=', 'products.id')
                ->whereDate('sales.created_at', '>=', $monthStart)
                ->whereDate('sales.created_at', '<=', $monthEnd)
                ->whereNull('sales.deleted_at')
                ->where(fn($q) => $q->where('sales.sale_type', '!=', 'opening balance')->orWhereNull('sales.sale_type'))
                ->selectRaw('product_sales.product_id, products.name as product_name, products.code as product_code, SUM(product_sales.qty - COALESCE(product_sales.return_qty, 0)) as sold_qty')
                ->groupBy('product_sales.product_id', 'products.name', 'products.code')
                ->orderByDesc('sold_qty')
                ->limit(1);

            if ($warehouseId) {
                $query->where('sales.warehouse_id', $warehouseId);
            }

            $best = $query->first();

            $data[] = [
                'month' => (int)$current->format('n'),
                'month_name' => $current->format('F Y'),
                'product_id' => $best?->product_id,
                'product_name' => $best ? $best->product_name . ': ' . $best->product_code : null,
                'sold_qty' => (float)($best?->sold_qty ?? 0),
            ];

            $current->addMonth();
        }

        return [
            'start_month' => $startDate->format('F Y'),
            'end_month' => $endDate->format('F Y'),
            'data' => $data,
        ];
    }

    public function exportSaleReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('sale-report');

        $sales = Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->whereNull('deleted_at')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['warehouse_id'] ?? null), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->orderByDesc('created_at')
            ->get();

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference No',
            'customer' => 'Customer',
            'warehouse' => 'Warehouse',
            'grand_total' => 'Grand Total',
        ];

        $rows = $sales->map(fn(Sale $s) => [
            'date' => $s->created_at?->format('Y-m-d'),
            'reference_no' => $s->reference_no,
            'customer' => $s->customer?->name ?? '—',
            'warehouse' => $s->warehouse?->name ?? '—',
            'grand_total' => round((float)$s->grand_total / ($s->exchange_rate ?: 1), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'sale_report_' . now()->timestamp,
            'Sale Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportPurchaseReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('purchase-report');

        $purchases = Purchase::query()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['warehouse_id'] ?? null), fn($q) => $q->where('warehouse_id', $filters['warehouse_id']))
            ->orderByDesc('created_at')
            ->get();

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference No',
            'supplier' => 'Supplier',
            'warehouse' => 'Warehouse',
            'grand_total' => 'Grand Total',
        ];

        $rows = $purchases->map(fn(Purchase $p) => [
            'date' => $p->created_at?->format('Y-m-d'),
            'reference_no' => $p->reference_no,
            'supplier' => $p->supplier?->name ?? '—',
            'warehouse' => $p->warehouse?->name ?? '—',
            'grand_total' => round((float)$p->grand_total / ($p->exchange_rate ?: 1), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'purchase_report_' . now()->timestamp,
            'Purchase Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportPaymentReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('payment-report');

        $query = Payment::query()
            ->with(['sale:id,reference_no', 'purchase:id,reference_no', 'user:id,name'])
            ->whereDate('payment_at', '>=', $filters['start_date'])
            ->whereDate('payment_at', '<=', $filters['end_date']);

        $type = $filters['type'] ?? 'all';
        if ($type === 'sale') {
            $query->whereNotNull('sale_id');
        } elseif ($type === 'purchase') {
            $query->whereNotNull('purchase_id');
        }

        $payments = $query->orderByDesc('payment_at')->get();

        $columnLabels = [
            'payment_at' => 'Payment Date',
            'amount' => 'Amount',
            'reference' => 'Reference',
            'user' => 'User',
        ];

        $rows = $payments->map(fn(Payment $p) => [
            'payment_at' => $p->payment_at?->format('Y-m-d H:i'),
            'amount' => round((float)$p->amount / ($p->exchange_rate ?: 1), 2),
            'reference' => $p->sale?->reference_no ?? $p->purchase?->reference_no ?? '—',
            'user' => $p->user?->name ?? '—',
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'payment_report_' . now()->timestamp,
            'Payment Report',
            $format,
            $user,
            $method,
            ['amount']
        );
    }

    public function exportSupplierDueReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('supplier-due-report');

        $purchases = Purchase::query()
            ->with('supplier:id,name,phone_number')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(!empty($filters['supplier_id'] ?? null), fn($q) => $q->where('supplier_id', $filters['supplier_id']))
            ->orderByDesc('created_at')
            ->get();

        $returnsByPurchase = ReturnPurchase::query()
            ->whereIn('purchase_id', $purchases->pluck('id'))
            ->get()
            ->groupBy('purchase_id')
            ->map(fn($returns) => $returns->sum(fn(ReturnPurchase $r) => $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total));

        $rows = $purchases->map(function (Purchase $purchase) use ($returnsByPurchase): array {
            $returnedAmount = (float)($returnsByPurchase[$purchase->id] ?? 0);
            $exchangeRate = $purchase->exchange_rate ?: 1;
            $grandTotal = (float)$purchase->grand_total / $exchangeRate;
            $paid = (float)($purchase->paid_amount ?? 0) / $exchangeRate;
            $due = max(0, $grandTotal - $returnedAmount - $paid);

            return [
                'date' => $purchase->created_at?->format('Y-m-d'),
                'reference_no' => $purchase->reference_no,
                'supplier_name' => $purchase->supplier?->name ?? '—',
                'supplier_phone' => $purchase->supplier?->phone_number ?? '—',
                'grand_total' => round($grandTotal, 2),
                'returned_amount' => round($returnedAmount, 2),
                'paid' => round($paid, 2),
                'due' => round($due, 2),
            ];
        });

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'supplier_name' => 'Supplier Name',
            'supplier_phone' => 'Supplier Phone',
            'grand_total' => 'Grand Total',
            'returned_amount' => 'Returned Amount',
            'paid' => 'Paid',
            'due' => 'Due',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'supplier_due_' . now()->timestamp,
            'Supplier Due Report',
            $format,
            $user,
            $method,
            ['grand_total', 'returned_amount', 'paid', 'due']
        );
    }

    public function exportChallanReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('packing_slip_challan');

        $basedOn = $filters['based_on'] ?? 'created_at';
        $challans = Challan::query()
            ->with('courier:id,name')
            ->closed()
            ->whereDate($basedOn, '>=', $filters['start_date'])
            ->whereDate($basedOn, '<=', $filters['end_date'])
            ->orderByDesc($basedOn)
            ->get();

        $columnLabels = [
            'id' => 'ID',
            'reference_no' => 'Reference No',
            'courier' => 'Courier',
            'created_at' => 'Created At',
        ];

        $rows = $challans->map(fn(Challan $c) => [
            'id' => $c->id,
            'reference_no' => $c->reference_no ?? '—',
            'courier' => $c->courier?->name ?? '—',
            'created_at' => $c->{$basedOn}?->format('Y-m-d H:i'),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'challan_' . now()->timestamp,
            'Challan Report',
            $format,
            $user,
            $method
        );
    }

    public function exportProductReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('product-report');

        $warehouseId = $filters['warehouse_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $items = ProductWarehouse::query()
            ->with(['product:id,name,code,cost,price,category_id', 'product.category:id,name', 'warehouse:id,name'])
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->when($categoryId, fn($q) => $q->where('products.category_id', $categoryId))
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->select('product_warehouse.*')
            ->orderBy('products.name')
            ->get();

        $columnLabels = [
            'product_name' => 'Product Name',
            'product_code' => 'Code',
            'category' => 'Category',
            'warehouse' => 'Warehouse',
            'qty' => 'Qty',
            'cost' => 'Cost',
            'price' => 'Price',
        ];

        $rows = $items->map(fn($item) => [
            'product_name' => $item->product?->name ?? '—',
            'product_code' => $item->product?->code ?? '—',
            'category' => $item->product?->category?->name ?? '—',
            'warehouse' => $item->warehouse?->name ?? '—',
            'qty' => (float)$item->qty,
            'cost' => round((float)($item->product?->cost ?? 0), 2),
            'price' => round((float)($item->product?->price ?? $item->price ?? 0), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'product_report_' . now()->timestamp,
            'Product Report',
            $format,
            $user,
            $method,
            ['qty', 'cost', 'price']
        );
    }

    public function exportCustomerReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('customer-report');

        $sales = Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name', 'productSales.product:id,name,code,cost'])
            ->whereNull('deleted_at')
            ->where('customer_id', $filters['customer_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->get();

        $rows = $sales->map(fn(Sale $sale) => $this->mapSaleToCustomerReportRow($sale));

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'warehouse' => 'Warehouse',
            'customer' => 'Customer',
            'product' => 'Product',
            'total_cost' => 'Total Cost',
            'grand_total' => 'Grand Total',
            'paid' => 'Paid',
            'due' => 'Due',
            'status' => 'Status',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'customer_report_' . now()->timestamp,
            'Customer Report',
            $format,
            $user,
            $method,
            ['total_cost', 'grand_total', 'paid', 'due']
        );
    }

    public function exportCustomerGroupReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('customer-group-report');

        $customerIds = Customer::query()
            ->where('customer_group_id', $filters['customer_group_id'])
            ->pluck('id');

        $sales = Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name', 'productSales.product:id,name,code,cost'])
            ->whereNull('deleted_at')
            ->whereIn('customer_id', $customerIds)
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->get();

        $rows = $sales->map(fn(Sale $sale) => $this->mapSaleToCustomerReportRow($sale));

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'warehouse' => 'Warehouse',
            'customer' => 'Customer',
            'product' => 'Product',
            'total_cost' => 'Total Cost',
            'grand_total' => 'Grand Total',
            'paid' => 'Paid',
            'due' => 'Due',
            'status' => 'Status',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'customer_group_report_' . now()->timestamp,
            'Customer Group Report',
            $format,
            $user,
            $method,
            ['total_cost', 'grand_total', 'paid', 'due']
        );
    }

    public function exportSupplierReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('supplier-report');

        $purchases = Purchase::query()
            ->with(['supplier:id,name,phone_number', 'warehouse:id,name'])
            ->where('supplier_id', $filters['supplier_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->get();

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'warehouse' => 'Warehouse',
            'supplier' => 'Supplier',
            'grand_total' => 'Grand Total',
        ];

        $rows = $purchases->map(fn(Purchase $p) => [
            'date' => $p->created_at?->format('Y-m-d'),
            'reference_no' => $p->reference_no,
            'warehouse' => $p->warehouse?->name ?? '—',
            'supplier' => $p->supplier?->name ?? '—',
            'grand_total' => round((float)$p->grand_total / ($p->exchange_rate ?: 1), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'supplier_report_' . now()->timestamp,
            'Supplier Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportUserReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('user-report');

        $filters['user_id'] = $filters['filter_user_id'] ?? null;

        $sales = Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name', 'user:id,name'])
            ->whereNull('deleted_at')
            ->where('user_id', $filters['user_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->get();

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'customer' => 'Customer',
            'warehouse' => 'Warehouse',
            'grand_total' => 'Grand Total',
        ];

        $rows = $sales->map(fn(Sale $s) => [
            'date' => $s->created_at?->format('Y-m-d'),
            'reference_no' => $s->reference_no,
            'customer' => $s->customer?->name ?? '—',
            'warehouse' => $s->warehouse?->name ?? '—',
            'grand_total' => round((float)$s->grand_total / ($s->exchange_rate ?: 1), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'user_report_' . now()->timestamp,
            'User Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportBillerReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('biller-report');

        $sales = Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->whereNull('deleted_at')
            ->where('biller_id', $filters['biller_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->get();

        $columnLabels = [
            'date' => 'Date',
            'reference_no' => 'Reference',
            'customer' => 'Customer',
            'warehouse' => 'Warehouse',
            'grand_total' => 'Grand Total',
        ];

        $rows = $sales->map(fn(Sale $s) => [
            'date' => $s->created_at?->format('Y-m-d'),
            'reference_no' => $s->reference_no,
            'customer' => $s->customer?->name ?? '—',
            'warehouse' => $s->warehouse?->name ?? '—',
            'grand_total' => round((float)$s->grand_total / ($s->exchange_rate ?: 1), 2),
        ]);

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'biller_report_' . now()->timestamp,
            'Biller Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportWarehouseReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('warehouse-report');

        $warehouseId = (int)$filters['warehouse_id'];
        $type = $filters['type'] ?? 'both';

        if ($type === 'purchases') {
            $items = Purchase::query()
                ->with(['supplier:id,name', 'warehouse:id,name'])
                ->where('warehouse_id', $warehouseId)
                ->whereDate('created_at', '>=', $filters['start_date'])
                ->whereDate('created_at', '<=', $filters['end_date'])
                ->orderByDesc('created_at')
                ->get();

            $columnLabels = [
                'date' => 'Date',
                'reference_no' => 'Reference',
                'supplier' => 'Supplier',
                'warehouse' => 'Warehouse',
                'grand_total' => 'Grand Total',
            ];

            $rows = $items->map(fn(Purchase $p) => [
                'date' => $p->created_at?->format('Y-m-d'),
                'reference_no' => $p->reference_no,
                'supplier' => $p->supplier?->name ?? '—',
                'warehouse' => $p->warehouse?->name ?? '—',
                'grand_total' => round((float)$p->grand_total / ($p->exchange_rate ?: 1), 2),
            ]);
        } else {
            $items = Sale::query()
                ->with(['customer:id,name', 'warehouse:id,name'])
                ->whereNull('deleted_at')
                ->where('warehouse_id', $warehouseId)
                ->whereDate('created_at', '>=', $filters['start_date'])
                ->whereDate('created_at', '<=', $filters['end_date'])
                ->orderByDesc('created_at')
                ->get();

            $columnLabels = [
                'date' => 'Date',
                'reference_no' => 'Reference',
                'customer' => 'Customer',
                'warehouse' => 'Warehouse',
                'grand_total' => 'Grand Total',
            ];

            $rows = $items->map(fn(Sale $s) => [
                'date' => $s->created_at?->format('Y-m-d'),
                'reference_no' => $s->reference_no,
                'customer' => $s->customer?->name ?? '—',
                'warehouse' => $s->warehouse?->name ?? '—',
                'grand_total' => round((float)$s->grand_total / ($s->exchange_rate ?: 1), 2),
            ]);
        }

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'warehouse_report_' . now()->timestamp,
            'Warehouse Report',
            $format,
            $user,
            $method,
            ['grand_total']
        );
    }

    public function exportProfitLoss(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('profit-loss');

        $report = $this->getProfitLoss($filters);
        $rows = collect([
            ['label' => 'Sales', 'value' => $report['sales']],
            ['label' => 'Returns', 'value' => $report['returns']],
            ['label' => 'Purchases', 'value' => $report['purchases']],
            ['label' => 'Purchase Returns', 'value' => $report['purchase_returns']],
            ['label' => 'Expenses', 'value' => $report['expenses']],
            ['label' => 'Income', 'value' => $report['income']],
            ['label' => 'Payroll', 'value' => $report['payroll']],
            ['label' => 'Payments', 'value' => $report['payments']],
            ['label' => 'Gross Profit', 'value' => $report['gross_profit']],
            ['label' => 'Net Profit', 'value' => $report['net_profit']],
        ]);

        $columnLabels = [
            'label' => 'Item',
            'value' => 'Amount',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'profit_loss_' . now()->timestamp,
            'Profit/Loss Report ' . $report['start_date'] . ' to ' . $report['end_date'],
            $format,
            $user,
            $method,
            ['value']
        );
    }

    /**
     * Retrieve profit/loss report - summary of sales, purchases, returns, expenses, income, payroll.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getProfitLoss(array $filters = []): array
    {
        $this->requirePermission('profit-loss');

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        $saleTotal = (float)Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'opening balance')->orWhereNull('sale_type');
            })
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $returnTotal = (float)Returns::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $purchaseTotal = (float)Purchase::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $purchaseReturnTotal = (float)ReturnPurchase::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $expenseTotal = (float)Expense::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $incomeTotal = (float)Income::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $payrollTotal = (float)Payroll::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $paymentTotal = (float)Payment::query()
            ->whereDate('payment_at', '>=', $startDate)
            ->whereDate('payment_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $grossProfit = $saleTotal - $returnTotal;
        $grossPurchase = $purchaseTotal - $purchaseReturnTotal;
        $netProfit = $grossProfit - $grossPurchase - $expenseTotal + $incomeTotal - $payrollTotal;

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'sales' => round($saleTotal, 2),
            'returns' => round($returnTotal, 2),
            'purchases' => round($purchaseTotal, 2),
            'purchase_returns' => round($purchaseReturnTotal, 2),
            'expenses' => round($expenseTotal, 2),
            'income' => round($incomeTotal, 2),
            'payroll' => round($payrollTotal, 2),
            'payments' => round($paymentTotal, 2),
            'gross_profit' => round($grossProfit, 2),
            'net_profit' => round($netProfit, 2),
        ];
    }

    public function exportSaleReportChart(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('sale-report-chart');

        $report = $this->getSaleReportChart($filters);
        $datePoints = $report['date_points'] ?? [];
        $soldQty = $report['sold_qty'] ?? [];
        $rows = collect();
        foreach ($datePoints as $i => $dp) {
            $rows->push([
                'date_point' => $dp,
                'sold_qty' => $soldQty[$i] ?? 0,
            ]);
        }

        $columnLabels = [
            'date_point' => 'Date',
            'sold_qty' => 'Sold Qty',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'sale_chart_' . now()->timestamp,
            'Sale Report Chart',
            $format,
            $user,
            $method,
            ['sold_qty']
        );
    }

    /**
     * Retrieve sale report chart data - date points with sold qty for charts.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getSaleReportChart(array $filters = []): array
    {
        $this->requirePermission('sale-report-chart');

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $warehouseId = $filters['warehouse_id'] ?? null;
        $timePeriod = $filters['time_period'] ?? 'monthly';
        $productList = $filters['product_list'] ?? null;

        $productIds = null;
        if (!empty($productList)) {
            $codes = array_map('trim', explode(',', $productList));
            $codes = array_filter($codes);
            if (!empty($codes)) {
                $productIds = Product::query()->whereIn('code', $codes)->pluck('id')->all();
            }
        }

        $datePoints = [];
        if ($timePeriod === 'monthly') {
            for ($i = strtotime($startDate); $i <= strtotime($endDate); $i = strtotime('+1 month', $i)) {
                $datePoints[] = date('Y-m-d', $i);
            }
        } else {
            for ($i = strtotime('Saturday', strtotime($startDate)); $i <= strtotime($endDate); $i = strtotime('+1 week', $i)) {
                $datePoints[] = date('Y-m-d', $i);
            }
        }
        $datePoints[] = $endDate;
        $datePoints = array_unique($datePoints);
        sort($datePoints);

        $soldQty = [];
        $runningStart = $startDate;
        foreach ($datePoints as $datePoint) {
            $q = DB::table('sales')
                ->join('product_sales', 'sales.id', '=', 'product_sales.sale_id')
                ->whereNull('sales.deleted_at')
                ->where(function ($q2) {
                    $q2->where('sales.sale_type', '!=', 'opening balance')->orWhereNull('sales.sale_type');
                })
                ->whereDate('sales.created_at', '>=', $runningStart)
                ->whereDate('sales.created_at', '<', $datePoint);
            if ($warehouseId) {
                $q->where('sales.warehouse_id', $warehouseId);
            }
            if ($productIds !== null && !empty($productIds)) {
                $q->whereIn('product_sales.product_id', $productIds);
            }
            $soldQty[] = (float)$q->selectRaw('COALESCE(SUM(product_sales.qty - COALESCE(product_sales.return_qty, 0)), 0) as net_qty')->value('net_qty');
            $runningStart = $datePoint;
        }

        return [
            'date_points' => $datePoints,
            'sold_qty' => $soldQty,
            'time_period' => $timePeriod,
        ];
    }

    public function exportDailySaleObjective(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('dso-report');

        $queryStart = date('Y-m-d', strtotime('+1 day', strtotime($filters['start_date'])));
        $queryEnd = date('Y-m-d', strtotime('+1 day', strtotime($filters['end_date'])));

        $rows = DB::table('dso_alerts')
            ->whereDate('created_at', '>=', $queryStart)
            ->whereDate('created_at', '<=', $queryEnd)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($row) {
                $decoded = json_decode($row->product_info);
                $productList = '';
                if (is_array($decoded)) {
                    $parts = array_map(function ($p) {
                        $name = is_object($p) ? ($p->name ?? '') : ($p['name'] ?? '');
                        $code = is_object($p) ? ($p->code ?? '') : ($p['code'] ?? '');

                        return $name . ' [' . $code . ']';
                    }, $decoded);
                    $productList = implode(', ', $parts);
                } elseif (is_object($decoded) && (isset($decoded->name) || isset($decoded->code))) {
                    $productList = ($decoded->name ?? '') . ' [' . ($decoded->code ?? '') . ']';
                }

                return [
                    'date' => date('Y-m-d', strtotime('-1 day', strtotime($row->created_at))),
                    'product_info' => $productList,
                    'number_of_products' => (int)$row->number_of_products,
                ];
            });

        $columnLabels = [
            'date' => 'Date',
            'product_info' => 'Products',
            'number_of_products' => 'Number of Products',
        ];

        return $this->exportReportTable(
            $rows,
            $columnLabels,
            $columns,
            'daily_sale_objective_' . now()->timestamp,
            'Daily Sale Objective Report',
            $format,
            $user,
            $method,
            ['number_of_products']
        );
    }
}
