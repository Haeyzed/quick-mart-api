<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\CustomerDueReportExport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Sale;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * Report service for customer due and other reports.
 */
class ReportService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Get paginated customer due report: unpaid sales in date range with returned amount, paid, and due.
     *
     * @return LengthAwarePaginator<int, array<string, mixed>>
     */
    public function getCustomerDueReport(
        string $startDate,
        string $endDate,
        ?int $customerId,
        int $page = 1,
        int $perPage = 15
    ): LengthAwarePaginator {
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

        $rows = $paginator->getCollection()->map(function (Sale $sale): array {
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

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $rows,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage(),
            ['path' => RequestFacade::url()]
        );
    }

    /**
     * Get all customer due report rows for export (no pagination).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function getCustomerDueReportForExport(
        string $startDate,
        string $endDate,
        ?int $customerId
    ): Collection {
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

        return $query->get()->map(function (Sale $sale): array {
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
    }

    /**
     * Export customer due report to Excel or PDF.
     *
     * @param  array<string>  $columns
     * @return string Relative storage path of the generated file.
     */
    public function exportCustomerDueReport(
        string $startDate,
        string $endDate,
        ?int $customerId,
        string $format,
        ?User $user,
        array $columns,
        string $method
    ): string {
        $this->requirePermission('due-report');

        $rows = $this->getCustomerDueReportForExport($startDate, $endDate, $customerId);

        $fileName = 'customer-due-report_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'excel') {
            Excel::store(new CustomerDueReportExport($rows, $columns), $relativePath, 'public');
        } else {
            $pdf = PDF::loadView('exports.customer-due-report-pdf', [
                'rows' => $rows,
                'columns' => $columns,
            ]);
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName);
        }

        return $relativePath;
    }

    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn () => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();
        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Customer Due Report', $generalSetting)
        );
    }
}
