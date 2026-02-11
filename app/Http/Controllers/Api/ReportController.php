<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\CustomerDueReportExportRequest;
use App\Http\Requests\Reports\CustomerDueReportRequest;
use App\Http\Resources\DueReportResource;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
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
     * Customer due report: unpaid sales in date range with returned amount, paid, and due.
     */
    public function customerDueReport(CustomerDueReportRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $paginator = $this->service->getCustomerDueReport(
            $validated['start_date'],
            $validated['end_date'],
            $validated['customer_id'] ?? null,
            (int) ($validated['page'] ?? 1),
            (int) ($validated['per_page'] ?? 15)
        );

        $paginator->through(fn (array $row) => new DueReportResource($row));

        return response()->success(
            $paginator,
            'Customer due report retrieved successfully'
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
            $validated['start_date'],
            $validated['end_date'],
            $validated['customer_id'] ?? null,
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
