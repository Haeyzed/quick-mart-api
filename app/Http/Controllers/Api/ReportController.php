<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\AuditLogExportRequest;
use App\Http\Requests\Reports\AuditLogIndexRequest;
use App\Http\Requests\Reports\BestSellerExportRequest;
use App\Http\Requests\Reports\BestSellerReportRequest;
use App\Http\Requests\Reports\BillerReportExportRequest;
use App\Http\Requests\Reports\BillerReportRequest;
use App\Http\Requests\Reports\ChallanReportExportRequest;
use App\Http\Requests\Reports\ChallanReportRequest;
use App\Http\Requests\Reports\CustomerDueReportExportRequest;
use App\Http\Requests\Reports\CustomerDueReportRequest;
use App\Http\Requests\Reports\CustomerGroupReportExportRequest;
use App\Http\Requests\Reports\CustomerGroupReportRequest;
use App\Http\Requests\Reports\CustomerReportExportRequest;
use App\Http\Requests\Reports\CustomerReportRequest;
use App\Http\Requests\Reports\DailyPurchaseExportRequest;
use App\Http\Requests\Reports\DailyPurchaseReportRequest;
use App\Http\Requests\Reports\DailySaleExportRequest;
use App\Http\Requests\Reports\DailySaleObjectiveExportRequest;
use App\Http\Requests\Reports\DailySaleObjectiveRequest;
use App\Http\Requests\Reports\DailySaleReportRequest;
use App\Http\Requests\Reports\MonthlyPurchaseExportRequest;
use App\Http\Requests\Reports\MonthlyPurchaseReportRequest;
use App\Http\Requests\Reports\MonthlySaleExportRequest;
use App\Http\Requests\Reports\MonthlySaleReportRequest;
use App\Http\Requests\Reports\PaymentReportExportRequest;
use App\Http\Requests\Reports\PaymentReportRequest;
use App\Http\Requests\Reports\ProductExpiryExportRequest;
use App\Http\Requests\Reports\ProductExpiryReportRequest;
use App\Http\Requests\Reports\ProductQtyAlertExportRequest;
use App\Http\Requests\Reports\ProductQtyAlertRequest;
use App\Http\Requests\Reports\ProductReportExportRequest;
use App\Http\Requests\Reports\ProductReportRequest;
use App\Http\Requests\Reports\ProfitLossExportRequest;
use App\Http\Requests\Reports\ProfitLossReportRequest;
use App\Http\Requests\Reports\PurchaseReportExportRequest;
use App\Http\Requests\Reports\PurchaseReportRequest;
use App\Http\Requests\Reports\SaleReportChartExportRequest;
use App\Http\Requests\Reports\SaleReportChartRequest;
use App\Http\Requests\Reports\SaleReportExportRequest;
use App\Http\Requests\Reports\SaleReportRequest;
use App\Http\Requests\Reports\SupplierDueExportRequest;
use App\Http\Requests\Reports\SupplierDueReportRequest;
use App\Http\Requests\Reports\SupplierReportExportRequest;
use App\Http\Requests\Reports\SupplierReportRequest;
use App\Http\Requests\Reports\UserReportExportRequest;
use App\Http\Requests\Reports\UserReportRequest;
use App\Http\Requests\Reports\WarehouseReportExportRequest;
use App\Http\Requests\Reports\WarehouseReportRequest;
use App\Http\Requests\Reports\WarehouseStockExportRequest;
use App\Http\Requests\Reports\WarehouseStockReportRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\DueReportResource;
use App\Http\Resources\ProductResource;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Models\Audit;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for reports.
 *
 * @group Reports
 */
class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $service
    ) {}

    /**
     * Customer due report: paginated listing of sales with outstanding balance.
     */
    public function customerDueReport(CustomerDueReportRequest $request): JsonResponse
    {
        $report = $this->service->getCustomerDueReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $report->through(fn ($row) => new DueReportResource($row));

        return response()->success(
            $report,
            'Customer due report fetched successfully'
        );
    }

    /**
     * Export customer due report to Excel or PDF.
     */
    public function exportCustomerDueReport(CustomerDueReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportCustomerDueReport(
            [
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'customer_id' => $validated['customer_id'] ?? null,
            ],
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
     * Audit log: paginated listing of audits.
     */
    public function auditLogIndex(AuditLogIndexRequest $request): JsonResponse
    {
        $audits = $this->service->getAudits(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $audits->through(fn (Audit $audit) => new AuditResource($audit));

        return response()->success(
            $audits,
            'Audits fetched successfully'
        );
    }

    /**
     * Export audit log to Excel or PDF.
     */
    public function auditLogExport(AuditLogExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();

        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportAudits(
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
     * Product quantity alert: products with qty below alert threshold.
     */
    public function productQtyAlert(ProductQtyAlertRequest $request): JsonResponse
    {
        $products = $this->service->getProductQtyAlert(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $products->through(fn ($product) => new ProductResource($product));

        return response()->success(
            $products,
            'Product quantity alert report fetched successfully'
        );
    }

    /**
     * Product expiry: product batches with stock and expiry dates.
     */
    public function productExpiry(ProductExpiryReportRequest $request): JsonResponse
    {
        $batches = $this->service->getProductExpiry(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $data = [
            'data' => $batches->items(),
            'meta' => [
                'current_page' => $batches->currentPage(),
                'last_page' => $batches->lastPage(),
                'per_page' => $batches->perPage(),
                'total' => $batches->total(),
            ],
        ];

        return response()->success($data, 'Product expiry report fetched successfully');
    }

    /**
     * Warehouse stock: stock summary and items by warehouse.
     */
    public function warehouseStock(WarehouseStockReportRequest $request): JsonResponse
    {
        $report = $this->service->getWarehouseStock($request->validated());

        $report['items']->through(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'warehouse_id' => $item->warehouse_id,
                'qty' => (float) $item->qty,
                'price' => $item->price,
                'product' => $item->product ? new ProductResource($item->product) : null,
                'warehouse' => $item->warehouse ? [
                    'id' => $item->warehouse->id,
                    'name' => $item->warehouse->name,
                ] : null,
            ];
        });

        return response()->success(
            $report,
            'Warehouse stock report fetched successfully'
        );
    }

    /**
     * Daily sale report: sale totals per day for a given month.
     */
    public function dailySale(DailySaleReportRequest $request): JsonResponse
    {
        $report = $this->service->getDailySale($request->validated());

        return response()->success($report, 'Daily sale report fetched successfully');
    }

    /**
     * Monthly sale report: sale totals per month for a given year.
     */
    public function monthlySale(MonthlySaleReportRequest $request): JsonResponse
    {
        $report = $this->service->getMonthlySale($request->validated());

        return response()->success($report, 'Monthly sale report fetched successfully');
    }

    /**
     * Daily purchase report: purchase totals per day for a given month.
     */
    public function dailyPurchase(DailyPurchaseReportRequest $request): JsonResponse
    {
        $report = $this->service->getDailyPurchase($request->validated());

        return response()->success($report, 'Daily purchase report fetched successfully');
    }

    /**
     * Monthly purchase report: purchase totals per month for a given year.
     */
    public function monthlyPurchase(MonthlyPurchaseReportRequest $request): JsonResponse
    {
        $report = $this->service->getMonthlyPurchase($request->validated());

        return response()->success($report, 'Monthly purchase report fetched successfully');
    }

    /**
     * Best seller report: top selling product per month.
     */
    public function bestSeller(BestSellerReportRequest $request): JsonResponse
    {
        $report = $this->service->getBestSeller($request->validated());

        return response()->success($report, 'Best seller report fetched successfully');
    }

    /**
     * Sale report: paginated sales in date range.
     */
    public function saleReport(SaleReportRequest $request): JsonResponse
    {
        $report = $this->service->getSaleReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Sale report fetched successfully');
    }

    /**
     * Purchase report: paginated purchases in date range.
     */
    public function purchaseReport(PurchaseReportRequest $request): JsonResponse
    {
        $report = $this->service->getPurchaseReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Purchase report fetched successfully');
    }

    /**
     * Payment report: paginated payments in date range.
     */
    public function paymentReport(PaymentReportRequest $request): JsonResponse
    {
        $report = $this->service->getPaymentReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Payment report fetched successfully');
    }

    /**
     * Supplier due report: purchases with outstanding balance.
     */
    public function supplierDueReport(SupplierDueReportRequest $request): JsonResponse
    {
        $report = $this->service->getSupplierDueReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Supplier due report fetched successfully');
    }

    /**
     * Challan report: closed challans in date range.
     */
    public function challanReport(ChallanReportRequest $request): JsonResponse
    {
        $report = $this->service->getChallanReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Challan report fetched successfully');
    }

    /**
     * Product report: products with stock, optionally filtered by warehouse and category.
     */
    public function productReport(ProductReportRequest $request): JsonResponse
    {
        $report = $this->service->getProductReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Product report fetched successfully');
    }

    /**
     * Customer report: sales by customer in date range.
     */
    public function customerReport(CustomerReportRequest $request): JsonResponse
    {
        $report = $this->service->getCustomerReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Customer report fetched successfully');
    }

    /**
     * Customer group report: sales by customers in the given group.
     */
    public function customerGroupReport(CustomerGroupReportRequest $request): JsonResponse
    {
        $report = $this->service->getCustomerGroupReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Customer group report fetched successfully');
    }

    /**
     * Supplier report: purchases by supplier in date range.
     */
    public function supplierReport(SupplierReportRequest $request): JsonResponse
    {
        $report = $this->service->getSupplierReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Supplier report fetched successfully');
    }

    /**
     * User report: sales by user in date range.
     */
    public function userReport(UserReportRequest $request): JsonResponse
    {
        $report = $this->service->getUserReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'User report fetched successfully');
    }

    /**
     * Biller report: sales by biller in date range.
     */
    public function billerReport(BillerReportRequest $request): JsonResponse
    {
        $report = $this->service->getBillerReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Biller report fetched successfully');
    }

    /**
     * Warehouse report: sales or purchases by warehouse.
     */
    public function warehouseReport(WarehouseReportRequest $request): JsonResponse
    {
        $report = $this->service->getWarehouseReport(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Warehouse report fetched successfully');
    }

    /**
     * Profit/loss report: summary for date range.
     */
    public function profitLoss(ProfitLossReportRequest $request): JsonResponse
    {
        $report = $this->service->getProfitLoss($request->validated());

        return response()->success($report, 'Profit/loss report fetched successfully');
    }

    /**
     * Sale report chart: date points and sold qty for charts.
     */
    public function saleReportChart(SaleReportChartRequest $request): JsonResponse
    {
        $report = $this->service->getSaleReportChart($request->validated());

        return response()->success($report, 'Sale report chart fetched successfully');
    }

    /**
     * Daily sale objective: DSO alerts from dso_alerts table.
     */
    public function dailySaleObjective(DailySaleObjectiveRequest $request): JsonResponse
    {
        $report = $this->service->getDailySaleObjective(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        return response()->success($report, 'Daily sale objective report fetched successfully');
    }

    private function handleExport(array $validated, callable $exportFn): JsonResponse|BinaryFileResponse
    {
        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $exportFn($validated, $user);

        if ($validated['method'] === 'download') {
            return response()->download(Storage::disk('public')->path($filePath));
        }

        return response()->success(null, 'Export processed and sent via email');
    }

    public function exportProductQtyAlert(ProductQtyAlertExportRequest $request): JsonResponse|BinaryFileResponse
    {
        return $this->handleExport($request->validated(), fn ($v, $u) => $this->service->exportProductQtyAlert([], $v['format'], $u, $v['columns'] ?? [], $v['method']));
    }

    public function exportProductExpiry(ProductExpiryExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportProductExpiry(
            array_filter(['expired_before' => $v['expired_before'] ?? null]),
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportWarehouseStock(WarehouseStockExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportWarehouseStock(
            array_filter(['warehouse_id' => $v['warehouse_id'] ?? null]),
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportDailySale(DailySaleExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportDailySale(
            ['year' => $v['year'], 'month' => $v['month'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportMonthlySale(MonthlySaleExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportMonthlySale(
            ['year' => $v['year'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportDailyPurchase(DailyPurchaseExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportDailyPurchase(
            ['year' => $v['year'], 'month' => $v['month'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportMonthlyPurchase(MonthlyPurchaseExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportMonthlyPurchase(
            ['year' => $v['year'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportBestSeller(BestSellerExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportBestSeller(
            ['months' => $v['months'] ?? null, 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportSaleReport(SaleReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportSaleReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportPurchaseReport(PurchaseReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportPurchaseReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'warehouse_id' => $v['warehouse_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportPaymentReport(PaymentReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportPaymentReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'type' => $v['type'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportSupplierDueReport(SupplierDueExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportSupplierDueReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'supplier_id' => $v['supplier_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportChallanReport(ChallanReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportChallanReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'based_on' => $v['based_on'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportProductReport(ProductReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportProductReport(
            ['warehouse_id' => $v['warehouse_id'] ?? null, 'category_id' => $v['category_id'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportCustomerReport(CustomerReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportCustomerReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'customer_id' => $v['customer_id']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportCustomerGroupReport(CustomerGroupReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportCustomerGroupReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'customer_group_id' => $v['customer_group_id']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportSupplierReport(SupplierReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportSupplierReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'supplier_id' => $v['supplier_id']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportUserReport(UserReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportUserReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'filter_user_id' => $v['filter_user_id']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportBillerReport(BillerReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportBillerReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'biller_id' => $v['biller_id']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportWarehouseReport(WarehouseReportExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportWarehouseReport(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'warehouse_id' => $v['warehouse_id'], 'type' => $v['type'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportProfitLoss(ProfitLossExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportProfitLoss(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportSaleReportChart(SaleReportChartExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportSaleReportChart(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date'], 'warehouse_id' => $v['warehouse_id'] ?? null, 'time_period' => $v['time_period'] ?? null, 'product_list' => $v['product_list'] ?? null],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }

    public function exportDailySaleObjective(DailySaleObjectiveExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $v = $request->validated();

        return $this->handleExport($v, fn ($_, $u) => $this->service->exportDailySaleObjective(
            ['start_date' => $v['start_date'], 'end_date' => $v['end_date']],
            $v['format'], $u, $v['columns'] ?? [], $v['method']
        ));
    }
}
