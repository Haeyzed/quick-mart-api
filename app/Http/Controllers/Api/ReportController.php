<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\AuditLogExportRequest;
use App\Http\Requests\Reports\AuditLogIndexRequest;
use App\Http\Requests\Reports\BestSellerReportRequest;
use App\Http\Requests\Reports\BillerReportRequest;
use App\Http\Requests\Reports\ChallanReportRequest;
use App\Http\Requests\Reports\CustomerDueReportExportRequest;
use App\Http\Requests\Reports\CustomerDueReportRequest;
use App\Http\Requests\Reports\CustomerGroupReportRequest;
use App\Http\Requests\Reports\CustomerReportRequest;
use App\Http\Requests\Reports\DailyPurchaseReportRequest;
use App\Http\Requests\Reports\DailySaleObjectiveRequest;
use App\Http\Requests\Reports\DailySaleReportRequest;
use App\Http\Requests\Reports\MonthlyPurchaseReportRequest;
use App\Http\Requests\Reports\MonthlySaleReportRequest;
use App\Http\Requests\Reports\PaymentReportRequest;
use App\Http\Requests\Reports\ProductExpiryReportRequest;
use App\Http\Requests\Reports\ProductQtyAlertRequest;
use App\Http\Requests\Reports\ProductReportRequest;
use App\Http\Requests\Reports\ProfitLossReportRequest;
use App\Http\Requests\Reports\PurchaseReportRequest;
use App\Http\Requests\Reports\SaleReportChartRequest;
use App\Http\Requests\Reports\SaleReportRequest;
use App\Http\Requests\Reports\SupplierDueReportRequest;
use App\Http\Requests\Reports\SupplierReportRequest;
use App\Http\Requests\Reports\UserReportRequest;
use App\Http\Requests\Reports\WarehouseReportRequest;
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
}
