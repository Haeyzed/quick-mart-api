<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\AuditsExport;
use App\Exports\CustomerDueReportExport;
use App\Mail\ExportMail;
use App\Models\Challan;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use OwenIt\Auditing\Models\Audit;
use RuntimeException;

/**
 * Report service for customer due, audit log, and other reports.
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
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<array<string, mixed>>
     */
    public function getCustomerDueReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('due-report');

        $query = Sale::query()
            ->with('customer')
            ->where('payment_status', '!=', 'paid')
            ->when(! empty($filters['start_date'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['end_date']))
            ->when(! empty($filters['customer_id'] ?? null), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->orderByDesc('created_at');

        $sales = $query->paginate($perPage);

        $rows = $sales->getCollection()->map(function (Sale $sale): array {
            $returnedAmount = Returns::query()
                ->where('sale_id', $sale->id)
                ->get()
                ->sum(fn (Returns $r) => $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total);

            $paid = (float) ($sale->paid_amount ?? 0);
            $grandTotal = (float) $sale->grand_total;
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
     * @param  array<string, mixed>  $filters
     * @param  array<string>  $columns
     * @return string Relative storage path of the generated file.
     */
    public function exportCustomerDueReport(array $filters, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('due-report');

        $sales = Sale::query()
            ->with('customer')
            ->where('payment_status', '!=', 'paid')
            ->when(! empty($filters['start_date'] ?? null), fn ($q) => $q->whereDate('created_at', '>=', $filters['start_date']))
            ->when(! empty($filters['end_date'] ?? null), fn ($q) => $q->whereDate('created_at', '<=', $filters['end_date']))
            ->when(! empty($filters['customer_id'] ?? null), fn ($q) => $q->where('customer_id', $filters['customer_id']))
            ->orderByDesc('created_at')
            ->get();

        $rows = $sales->map(function (Sale $sale): array {
            $returnedAmount = Returns::query()
                ->where('sale_id', $sale->id)
                ->get()
                ->sum(fn (Returns $r) => $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total);

            $paid = (float) ($sale->paid_amount ?? 0);
            $grandTotal = (float) $sale->grand_total;
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

        $fileName = 'customer_due_report_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

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

    /**
     * Retrieve product quantity alert report.
     *
     * Products where qty is below alert_quantity (is_active only).
     *
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
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
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<ProductBatch>
     */
    public function getProductExpiry(array $filters = [], int $perPage = 10)
    {
        $this->requirePermission('product-expiry-report');

        return ProductBatch::query()
            ->with('product:id,name,code,image')
            ->where('product_batches.qty', '>', 0)
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->when(! empty($filters['expired_before'] ?? null), fn ($q) => $q->whereDate('expired_date', '<=', $filters['expired_before']))
            ->orderBy('expired_date')
            ->paginate($perPage);
    }

    /**
     * Retrieve warehouse stock report with optional warehouse filter.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getWarehouseStock(array $filters = []): array
    {
        $this->requirePermission('warehouse-stock-report');

        $warehouseId = $filters['warehouse_id'] ?? null;

        $baseQuery = ProductWarehouse::query()
            ->join('products', 'product_warehouse.product_id', '=', 'products.id')
            ->where('products.is_active', true)
            ->when($warehouseId, fn ($q) => $q->where('product_warehouse.warehouse_id', $warehouseId));

        $totalItem = (clone $baseQuery)->where('product_warehouse.qty', '>', 0)->count();
        $totalQty = (float) ((clone $baseQuery)->sum('product_warehouse.qty'));
        $totalPrice = (float) ((clone $baseQuery)->selectRaw('SUM(COALESCE(products.price, 0) * product_warehouse.qty) as total')->value('total') ?? 0);
        $totalCost = (float) ((clone $baseQuery)->selectRaw('SUM(COALESCE(products.cost, 0) * product_warehouse.qty) as total')->value('total') ?? 0);

        $itemsQuery = ProductWarehouse::query()
            ->with(['product:id,name,code,image,cost,price', 'warehouse:id,name'])
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->orderBy('product_warehouse.id');

        $perPage = (int) ($filters['per_page'] ?? 15);
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
     * Retrieve daily sale report for a given month.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getDailySale(array $filters = []): array
    {
        $this->requirePermission('daily-sale');

        $year = (int) $filters['year'];
        $month = (int) $filters['month'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $query = Sale::query()
                ->whereDate('created_at', $date)
                ->selectRaw('
                    COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                    COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                    COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                    COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                    COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                    COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
                ');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $row = $query->first();
            $data[] = [
                'day' => $day,
                'date' => $date,
                'total_discount' => round((float) ($row->total_discount ?? 0), 2),
                'order_discount' => round((float) ($row->order_discount ?? 0), 2),
                'total_tax' => round((float) ($row->total_tax ?? 0), 2),
                'order_tax' => round((float) ($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float) ($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float) ($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'data' => $data,
        ];
    }

    /**
     * Retrieve monthly sale report for a given year.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getMonthlySale(array $filters = []): array
    {
        $this->requirePermission('monthly-sale');

        $year = (int) $filters['year'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $startDate = sprintf('%04d-%02d-01', $year, $m);
            $endDate = date('Y-m-t', strtotime($startDate));

            $query = Sale::query()
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('
                    COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                    COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                    COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                    COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                    COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                    COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
                ');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $row = $query->first();
            $data[] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'total_discount' => round((float) ($row->total_discount ?? 0), 2),
                'order_discount' => round((float) ($row->order_discount ?? 0), 2),
                'total_tax' => round((float) ($row->total_tax ?? 0), 2),
                'order_tax' => round((float) ($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float) ($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float) ($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'data' => $data,
        ];
    }

    /**
     * Retrieve daily purchase report for a given month.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getDailyPurchase(array $filters = []): array
    {
        $this->requirePermission('daily-purchase');

        $year = (int) $filters['year'];
        $month = (int) $filters['month'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
        $data = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $query = Purchase::query()
                ->whereDate('created_at', $date)
                ->selectRaw('
                    COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                    COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                    COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                    COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                    COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                    COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
                ');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $row = $query->first();
            $data[] = [
                'day' => $day,
                'date' => $date,
                'total_discount' => round((float) ($row->total_discount ?? 0), 2),
                'order_discount' => round((float) ($row->order_discount ?? 0), 2),
                'total_tax' => round((float) ($row->total_tax ?? 0), 2),
                'order_tax' => round((float) ($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float) ($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float) ($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'days_in_month' => $daysInMonth,
            'data' => $data,
        ];
    }

    /**
     * Retrieve monthly purchase report for a given year.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getMonthlyPurchase(array $filters = []): array
    {
        $this->requirePermission('monthly-purchase');

        $year = (int) $filters['year'];
        $warehouseId = $filters['warehouse_id'] ?? null;

        $data = [];
        for ($m = 1; $m <= 12; $m++) {
            $startDate = sprintf('%04d-%02d-01', $year, $m);
            $endDate = date('Y-m-t', strtotime($startDate));

            $query = Purchase::query()
                ->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate)
                ->selectRaw('
                    COALESCE(SUM(total_discount / NULLIF(exchange_rate, 0)), 0) as total_discount,
                    COALESCE(SUM(order_discount / NULLIF(exchange_rate, 0)), 0) as order_discount,
                    COALESCE(SUM(total_tax / NULLIF(exchange_rate, 0)), 0) as total_tax,
                    COALESCE(SUM(order_tax / NULLIF(exchange_rate, 0)), 0) as order_tax,
                    COALESCE(SUM(shipping_cost / NULLIF(exchange_rate, 0)), 0) as shipping_cost,
                    COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as grand_total
                ');

            if ($warehouseId) {
                $query->where('warehouse_id', $warehouseId);
            }

            $row = $query->first();
            $data[] = [
                'month' => $m,
                'month_name' => date('F', mktime(0, 0, 0, $m, 1)),
                'total_discount' => round((float) ($row->total_discount ?? 0), 2),
                'order_discount' => round((float) ($row->order_discount ?? 0), 2),
                'total_tax' => round((float) ($row->total_tax ?? 0), 2),
                'order_tax' => round((float) ($row->order_tax ?? 0), 2),
                'shipping_cost' => round((float) ($row->shipping_cost ?? 0), 2),
                'grand_total' => round((float) ($row->grand_total ?? 0), 2),
            ];
        }

        return [
            'year' => $year,
            'data' => $data,
        ];
    }

    /**
     * Retrieve best seller report - top selling product per month.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getBestSeller(array $filters = []): array
    {
        $this->requirePermission('best-seller');

        $months = (int) ($filters['months'] ?? 3);
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
                ->selectRaw('product_sales.product_id, SUM(product_sales.qty - COALESCE(product_sales.return_qty, 0)) as sold_qty')
                ->groupBy('product_sales.product_id')
                ->orderByDesc('sold_qty')
                ->limit(1);

            if ($warehouseId) {
                $query->where('sales.warehouse_id', $warehouseId);
            }

            $best = $query->first();

            $data[] = [
                'month' => (int) $current->format('n'),
                'month_name' => $current->format('F Y'),
                'product_id' => $best?->product_id,
                'product_name' => $best ? Product::find($best->product_id)?->name.': '.Product::find($best->product_id)?->code : null,
                'sold_qty' => (float) ($best?->sold_qty ?? 0),
            ];

            $current->addMonth();
        }

        return [
            'start_month' => $startDate->format('F Y'),
            'end_month' => $endDate->format('F Y'),
            'data' => $data,
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
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(! empty($filters['warehouse_id'] ?? null), fn ($q) => $q->where('warehouse_id', $filters['warehouse_id']))
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
            ->when(! empty($filters['warehouse_id'] ?? null), fn ($q) => $q->where('warehouse_id', $filters['warehouse_id']))
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
            ->with('supplier')
            ->where('payment_status', '!=', 'paid')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->when(! empty($filters['supplier_id'] ?? null), fn ($q) => $q->where('supplier_id', $filters['supplier_id']))
            ->orderByDesc('created_at');

        $purchases = $query->paginate($perPage);

        $rows = $purchases->getCollection()->map(function (Purchase $purchase): array {
            $returnedAmount = ReturnPurchase::query()
                ->where('purchase_id', $purchase->id)
                ->get()
                ->sum(fn (ReturnPurchase $r) => $r->exchange_rate ? $r->grand_total / $r->exchange_rate : $r->grand_total);

            $paid = (float) ($purchase->paid_amount ?? 0);
            $grandTotal = (float) $purchase->grand_total;
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
     * @param  array<string, mixed>  $filters
     */
    public function getProductReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('product-report');

        $warehouseId = $filters['warehouse_id'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $query = ProductWarehouse::query()
            ->with(['product:id,name,code,image,cost,price,category_id', 'product.category:id,name', 'warehouse:id,name'])
            ->whereHas('product', fn ($q) => $q->where('is_active', true)->when($categoryId, fn ($q2) => $q2->where('category_id', $categoryId)))
            ->where('product_warehouse.qty', '>', 0)
            ->when($warehouseId, fn ($q) => $q->where('product_warehouse.warehouse_id', $warehouseId))
            ->orderBy('product_warehouse.id');

        return $query->paginate($perPage);
    }

    /**
     * Retrieve customer report - sales by customer in date range.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getCustomerReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customer-report');

        return Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->where('customer_id', $filters['customer_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve customer group report - sales by customers in the given group.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getCustomerGroupReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customer-group-report');

        $customerIds = \App\Models\Customer::query()
            ->where('customer_group_id', $filters['customer_group_id'])
            ->pluck('id');

        return Sale::query()
            ->with(['customer:id,name,phone_number', 'warehouse:id,name'])
            ->whereIn('customer_id', $customerIds)
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve supplier report - purchases by supplier in date range.
     *
     * @param  array<string, mixed>  $filters
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
     * @param  array<string, mixed>  $filters
     */
    public function getUserReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('user-report');

        return Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name', 'user:id,name'])
            ->where('user_id', $filters['user_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve biller report - sales by biller in date range.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getBillerReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('biller-report');

        return Sale::query()
            ->with(['customer:id,name', 'warehouse:id,name'])
            ->where('biller_id', $filters['biller_id'])
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve warehouse report - sales and/or purchases by warehouse.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getWarehouseReport(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('warehouse-report');

        $warehouseId = (int) $filters['warehouse_id'];
        $type = $filters['type'] ?? 'both';

        if ($type === 'sales') {
            return Sale::query()
                ->with(['customer:id,name', 'warehouse:id,name'])
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
            ->where('warehouse_id', $warehouseId)
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Retrieve profit/loss report - summary of sales, purchases, returns, expenses, income, payroll.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getProfitLoss(array $filters = []): array
    {
        $this->requirePermission('profit-loss');

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];

        $saleTotal = (float) Sale::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->where('sale_type', '!=', 'opening balance')->orWhereNull('sale_type');
            })
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $returnTotal = (float) Returns::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $purchaseTotal = (float) Purchase::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $purchaseReturnTotal = (float) ReturnPurchase::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(grand_total / NULLIF(exchange_rate, 0)), 0) as total')
            ->value('total');

        $expenseTotal = (float) Expense::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $incomeTotal = (float) Income::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $payrollTotal = (float) Payroll::query()
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->value('total');

        $paymentTotal = (float) Payment::query()
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

    /**
     * Retrieve sale report chart data - date points with sold qty for charts.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getSaleReportChart(array $filters = []): array
    {
        $this->requirePermission('sale-report-chart');

        $startDate = $filters['start_date'];
        $endDate = $filters['end_date'];
        $warehouseId = $filters['warehouse_id'] ?? null;
        $timePeriod = $filters['time_period'] ?? 'monthly';

        $datePoints = [];
        if ($timePeriod === 'monthly') {
            for ($i = strtotime($startDate); $i <= strtotime($endDate); $i = strtotime('+1 month', $i)) {
                $datePoints[] = date('Y-m-d', $i);
            }
        } else {
            for ($i = strtotime('Sunday', strtotime($startDate)); $i <= strtotime($endDate); $i = strtotime('+1 week', $i)) {
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
                ->whereDate('sales.created_at', '>=', $runningStart)
                ->whereDate('sales.created_at', '<', $datePoint);
            if ($warehouseId) {
                $q->where('sales.warehouse_id', $warehouseId);
            }
            $soldQty[] = (float) $q->sum('product_sales.qty');
            $runningStart = $datePoint;
        }

        return [
            'date_points' => $datePoints,
            'sold_qty' => $soldQty,
            'time_period' => $timePeriod,
        ];
    }

    /**
     * Retrieve daily sale objective report - DSO alerts from dso_alerts table.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getDailySaleObjective(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('dso-report');

        $query = DB::table('dso_alerts')
            ->whereDate('created_at', '>=', $filters['start_date'])
            ->whereDate('created_at', '<=', $filters['end_date'])
            ->orderByDesc('created_at');

        $total = $query->count();
        $page = (int) ($filters['page'] ?? 1);
        $perPage = (int) ($filters['per_page'] ?? 10);
        $offset = ($page - 1) * $perPage;

        $rows = $query->offset($offset)->limit($perPage)->get();

        $data = $rows->map(function ($row, $key) {
            $productInfo = json_decode($row->product_info, true);
            $productList = is_array($productInfo)
                ? implode(', ', array_map(fn ($p) => ($p['name'] ?? '').' ['.($p['code'] ?? '').']', $productInfo))
                : '';

            return [
                'id' => $row->id,
                'key' => $key,
                'date' => date('Y-m-d', strtotime('-1 day', strtotime($row->created_at))),
                'product_info' => $productList,
                'number_of_products' => (int) $row->number_of_products,
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
     * Role-based: users with role_id > 2 only see their own audits.
     * Requires audit-logs-index permission.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Audit>
     */
    public function getAudits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('audit-logs-index');

        $user = Auth::user();

        $query = Audit::query()
            ->with('user')
            ->when($user && $user->role_id > 2, fn ($q) => $q->where('audits.user_id', $user->id))
            ->when(! empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($subQ) => $subQ
                    ->where('audits.event', 'like', $term)
                    ->orWhere('audits.auditable_type', 'like', $term)
                    ->orWhere('audits.auditable_id', 'like', $term)
                    ->orWhere('audits.tags', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                );
            })
            ->when(! empty($filters['event'] ?? null), fn ($q) => $q->where('audits.event', $filters['event']))
            ->when(! empty($filters['auditable_type'] ?? null), fn ($q) => $q->where('audits.auditable_type', 'like', "%{$filters['auditable_type']}%"))
            ->when(! empty($filters['ip_address'] ?? null), fn ($q) => $q->where('audits.ip_address', 'like', "%{$filters['ip_address']}%"))
            ->when(! empty($filters['user'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['user']}%";
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term));
            })
            ->when(! empty($filters['date_from'] ?? null), fn ($q) => $q->whereDate('audits.created_at', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to'] ?? null), fn ($q) => $q->whereDate('audits.created_at', '<=', $filters['date_to']))
            ->orderByDesc('audits.id');

        return $query->paginate($perPage);
    }

    /**
     * Export audits to Excel or PDF.
     *
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     * @return string Relative storage path of the generated file.
     */
    public function exportAudits(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('audit-logs-export');

        $fileName = 'audits_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'excel') {
            Excel::store(new AuditsExport($ids, $columns), $relativePath, 'public');
        } else {
            $audits = Audit::query()
                ->with('user')
                ->when(! empty($ids), fn ($q) => $q->whereIn('id', $ids))
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

    private function sendExportEmail(User $user, string $path, string $fileName, string $title = 'Customer Due Report'): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn () => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();
        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, $title, $generalSetting)
        );
    }
}
