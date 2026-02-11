<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Sale;
use App\Traits\CheckPermissionsTrait;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Report service for customer due and other reports.
 */
class ReportService
{
    use CheckPermissionsTrait;

    /**
     * Get paginated customer due report: unpaid sales in date range with returned amount, paid, and due.
     *
     * @return array{data: Collection<int, array<string, mixed>>, meta: array<string, mixed>}
     */
    public function getCustomerDueReport(
        string $startDate,
        string $endDate,
        ?int $customerId,
        int $page = 1,
        int $perPage = 15
    ): array {
        $this->requirePermission('due-report');

        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $query = Sale::query()
            ->unpaid()
            ->whereNull('sales.deleted_at')
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($customerId !== null, fn ($q) => $q->where('sales.customer_id', $customerId))
            ->with('customer')
            ->withSum('return', 'grand_total')
            ->orderBy('sales.created_at');

        $paginator = $query->paginate(perPage: $perPage, page: $page);

        $data = $paginator->getCollection()->map(function (Sale $sale): array {
            $returnedAmount = (float) ($sale->return_grand_total_sum ?? 0);
            $paid = (float) $sale->paid_amount;
            $due = max(0, (float) $sale->grand_total - $returnedAmount - $paid);
            $customer = $sale->customer;

            return [
                'id' => $sale->id,
                'date' => $sale->created_at?->toDateString(),
                'reference_no' => $sale->reference_no,
                'customer_name' => $customer?->name ?? '—',
                'customer_phone' => $customer?->phone_number ?? '—',
                'grand_total' => round((float) $sale->grand_total, 2),
                'returned_amount' => round($returnedAmount, 2),
                'paid' => round($paid, 2),
                'due' => round($due, 2),
            ];
        });

        return [
            'data' => $data,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ];
    }
}
