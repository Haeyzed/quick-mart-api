<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CustomerDueReportRequest;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

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
     * Customer due report: unpaid sales in date range with returned amount, paid, and due.
     */
    public function customerDueReport(CustomerDueReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->service->getCustomerDueReport(
            $validated['start_date'],
            $validated['end_date'],
            $validated['customer_id'] ?? null,
            (int) ($validated['page'] ?? 1),
            (int) ($validated['per_page'] ?? 15)
        );

        return response()->success(
            [
                'data' => $result['data'],
                'meta' => $result['meta'],
            ],
            'Customer due report retrieved successfully'
        );
    }
}
