<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeaveStatusEnum;
use App\Enums\OvertimeStatusEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\PayrollStatusEnum;
use App\Exports\PayrollsExport;
use App\Imports\PayrollsImport;
use App\Mail\PayrollDetails;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\Leave;
use App\Models\MailSetting;
use App\Models\Overtime;
use App\Models\Payment;
use App\Models\Payroll;
use App\Models\Sale;
use App\Traits\MailInfo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class PayrollService
 *
 * Handles all core business logic and database interactions for Payrolls.
 * Acts as the intermediary between the controllers and the database layer.
 */
class PayrollService
{
    use MailInfo;

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated payrolls based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @param  int  $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginatedPayrolls(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Payroll::query()
            ->with(['employee', 'user', 'account'])
            ->filter($filters)
            ->latest();

        $generalSetting = DB::table('general_settings')->latest()->first();
        if (Auth::check() && Auth::user() && $generalSetting && $generalSetting->staff_access == 'own') {
            $query->where('user_id', Auth::id());
        }

        return $query->paginate($perPage);
    }

    /**
     * Generate prospective payroll data.
     * MODERN STANDARD: Eliminated N+1 queries by using high-performance aggregated subqueries.
     *
     * @param string $month Format: Y-m
     * @param int|null $warehouseId
     * @param array|null $employeeIds
     * @return array<mixed>
     */
    public function getGenerationData(string $month, ?int $warehouseId, ?array $employeeIds): array
    {
        $monthStart = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $monthEnd = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $query = Employee::query()->where('is_active', true);
        if (!empty($employeeIds)) {
            $query->whereIn('id', $employeeIds);
        } elseif ($warehouseId) {
            $query->whereHas('user', fn($q) => $q->where('warehouse_id', $warehouseId));
        }
        $employees = $query->get();

        if ($employees->isEmpty()) {
            return [];
        }

        // Mass Extract IDs for blazing-fast IN clauses
        $empIds = $employees->pluck('id')->toArray();
        $userIds = $employees->pluck('user_id')->filter()->toArray();

        // Pre-fetch and map existing data to prevent loops hitting the database
        $existingPayrolls = Payroll::query()
            ->where('month', $month)
            ->whereIn('employee_id', $empIds)
            ->get()
            ->keyBy('employee_id');

        $attendances = Attendance::query()
            ->whereIn('employee_id', $empIds)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->get()
            ->groupBy('employee_id');

        $leaves = Leave::query()
            ->whereIn('employee_id', $empIds)
            ->where('status', LeaveStatusEnum::APPROVED->value)
            ->where(function ($q) use ($monthStart, $monthEnd) {
                $q->whereBetween('start_date', [$monthStart, $monthEnd])
                    ->orWhereBetween('end_date', [$monthStart, $monthEnd])
                    ->orWhere(function ($q2) use ($monthStart, $monthEnd) {
                        $q2->where('start_date', '<', $monthStart)->where('end_date', '>', $monthEnd);
                    });
            })
            ->get()
            ->groupBy('employee_id');

        // High-performance sum aggregates via database
        $expenses = Expense::query()
            ->whereIn('employee_id', $empIds)
            ->where('expense_category_id', 0)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->selectRaw('employee_id, SUM(amount) as total')
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id');

        $overtimes = Overtime::query()
            ->whereIn('employee_id', $empIds)
            ->where('status', OvertimeStatusEnum::APPROVED->value)
            ->whereBetween('date', [$monthStart, $monthEnd])
            ->selectRaw('employee_id, SUM(amount) as total_amount, SUM(hours) as total_hours')
            ->groupBy('employee_id')
            ->get()
            ->keyBy('employee_id');

        $salesOutputs = Payment::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('payment_at', [$monthStart, $monthEnd])
            ->selectRaw('user_id, SUM(amount) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $salesForCommission = Sale::query()
            ->whereIn('user_id', $userIds)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->selectRaw('user_id, SUM(grand_total) as total')
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $data = [];

        foreach ($employees as $employee) {
            $existingPayroll = $existingPayrolls->get($employee->id);
            $empAttendances = $attendances->get($employee->id, collect());

            $attendanceDates = $empAttendances->pluck('date')->map(fn($d) => Carbon::parse($d)->toDateString())->toArray();

            // Leaves Logic
            $totalLeaveDays = 0;
            $empLeaves = $leaves->get($employee->id, collect());
            foreach ($empLeaves as $leave) {
                $start = Carbon::parse($leave->start_date)->greaterThan($monthStart) ? $leave->start_date : $monthStart;
                $end = Carbon::parse($leave->end_date)->lessThan($monthEnd) ? $leave->end_date : $monthEnd;

                for ($date = Carbon::parse($start); $date->lte(Carbon::parse($end)); $date->addDay()) {
                    if (!in_array($date->toDateString(), $attendanceDates)) {
                        $totalLeaveDays++;
                    }
                }
            }

            // Attendance & Hours
            $attendanceDays = count($attendanceDates);
            $totalHours = 0;
            foreach ($empAttendances as $att) {
                if ($att->checkin && $att->checkout) {
                    $totalHours += Carbon::parse($att->checkout)->diffInMinutes(Carbon::parse($att->checkin)) / 60;
                }
            }

            // Total Sales for Agents
            $totalSalesOutput = 0;
            if ($employee->is_sale_agent && $employee->user_id) {
                $totalSalesOutput = (float) $salesOutputs->get($employee->user_id, 0);
            }

            // Commission Logic (Sales Target Based)
            $commission = 0;
            if ($employee->is_sale_agent == 1 && $employee->user_id) {
                $totalSalesForComm = (float) $salesForCommission->get($employee->user_id, 0);
                $targets = is_string($employee->sales_target) ? json_decode($employee->sales_target, true) : $employee->sales_target;

                if (is_array($targets)) {
                    $maxCommission = 0;
                    foreach ($targets as $target) {
                        $from = (float)($target['sales_from'] ?? 0);
                        $to = (float)($target['sales_to'] ?? 0);
                        $percent = (float)($target['percent'] ?? 0);

                        if ($totalSalesForComm >= $from && $totalSalesForComm <= $to) {
                            $calcCommission = ($totalSalesForComm * $percent) / 100;
                            if ($calcCommission > $maxCommission) {
                                $maxCommission = $calcCommission;
                            }
                        }
                    }
                    $commission = $maxCommission;
                }
            }

            // Expenses & Overtime Pre-Fetched Extractions
            $empExpense = (float) $expenses->get($employee->id, 0);
            $empOvertime = $overtimes->get($employee->id);
            $overtimeAmount = $empOvertime ? (float) $empOvertime->total_amount : 0;
            $overtimeHours = $empOvertime ? (float) $empOvertime->total_hours : 0;

            $baseData = [
                'employee' => clone $employee,
                'stats' => [
                    'total_leaves' => $totalLeaveDays,
                    'attendance_days' => $attendanceDays,
                    'total_work_hours' => number_format($totalHours, 2),
                    'total_sales' => $totalSalesOutput,
                    'overtime_hours' => $overtimeHours,
                ]
            ];

            if ($existingPayroll) {
                $amountArray = is_string($existingPayroll->amount_array) ? json_decode($existingPayroll->amount_array, true) : $existingPayroll->amount_array;
                $data[] = array_merge($baseData, [
                    'is_existing' => true,
                    'salary' => $amountArray['salary'] ?? ($existingPayroll->amount ?? 0),
                    'commission' => $amountArray['commission'] ?? 0,
                    'expense' => $amountArray['expense'] ?? $empExpense,
                    'overtime' => $amountArray['overtime'] ?? $overtimeAmount,
                    'total_amount' => $amountArray['total'] ?? ($existingPayroll->amount ?? 0),
                    'paying_method' => $existingPayroll->paying_method ?? PaymentMethodEnum::CASH->value,
                    'note' => $existingPayroll->note ?? '',
                    'status' => $existingPayroll->status?->value ?? PayrollStatusEnum::DRAFT->value,
                    'date' => Carbon::parse($existingPayroll->created_at)->format('d-m-Y'),
                ]);
            } else {
                $data[] = array_merge($baseData, [
                    'is_existing' => false,
                    'salary' => $employee->basic_salary,
                    'commission' => $commission,
                    'expense' => $empExpense,
                    'overtime' => $overtimeAmount,
                    'total_amount' => 0,
                    'paying_method' => PaymentMethodEnum::CASH->value,
                    'note' => '',
                    'status' => PayrollStatusEnum::DRAFT->value,
                    'date' => now()->format('d-m-Y'),
                ]);
            }
        }

        return $data;
    }

    /**
     * Process bulk payroll generation and payments.
     * MODERN STANDARD: Pre-fetches references to cleanly eradicate N+1 mail logic.
     *
     * @param string $month Format: Y-m
     * @param array<mixed> $payrolls
     * @param int|null $globalAccountId
     * @param PayrollStatusEnum $globalStatus
     * @return void
     */
    public function processBulkPayrolls(string $month, array $payrolls, ?int $globalAccountId, PayrollStatusEnum $globalStatus): void
    {
        if (empty($payrolls)) {
            return;
        }

        DB::transaction(function () use ($month, $payrolls, $globalAccountId, $globalStatus) {
            $employeeIds = collect($payrolls)->pluck('employee_id')->filter()->toArray();

            // Pre-fetch objects to prevent N+1 Queries
            $existingPayrolls = Payroll::query()
                ->where('month', $month)
                ->whereIn('employee_id', $employeeIds)
                ->get()
                ->keyBy('employee_id');

            $employees = Employee::query()->whereIn('id', $employeeIds)->get()->keyBy('id');

            $mailSetting = MailSetting::latest()->first();
            if ($mailSetting) {
                $this->setMailInfo($mailSetting);
            }

            foreach ($payrolls as $empId => $row) {
                if (!isset($row['employee_id']) || !isset($row['amount'])) continue;

                $employeeId = $row['employee_id'];
                $referenceNo = 'payroll-' . date("Ymd") . '-' . date("His") . '-' . $employeeId;

                $salary = (float)$row['amount'];
                $expense = (float)($row['expense'] ?? 0);
                $overtime = (float)($row['overtime'] ?? 0);
                $commission = (float)($row['commission'] ?? 0);

                $total = $salary + $commission + $overtime - $expense;

                $amountArray = [
                    'salary' => $salary,
                    'commission' => $commission,
                    'expense' => $expense,
                    'overtime' => $overtime,
                    'total' => $total,
                ];

                $payrollData = [
                    'user_id' => Auth::id(),
                    'account_id' => $globalAccountId ?? 0,
                    'amount' => $total,
                    'paying_method' => $row['paying_method'] ?? PaymentMethodEnum::CASH->value,
                    'note' => $row['note'] ?? null,
                    'status' => $globalStatus->value,
                    'amount_array' => $amountArray,
                ];

                $payroll = $existingPayrolls->get($employeeId);

                if ($payroll) {
                    // Update overrides reference_no historically in the codebase.
                    $payrollData['reference_no'] = $referenceNo;
                    $payroll->update($payrollData);
                } else {
                    $payrollData['reference_no'] = $referenceNo;
                    $payrollData['employee_id'] = $employeeId;
                    $payrollData['month'] = $month;
                    $payroll = Payroll::query()->create($payrollData);
                }

                $employee = $employees->get($employeeId);
                if ($employee && $employee->email && $mailSetting) {
                    try {
                        Mail::to($employee->email)->queue(new PayrollDetails([
                            'reference_no' => $referenceNo,
                            'amount' => $total,
                            'name' => $employee->name,
                            'email' => $employee->email,
                            'currency' => config('currency'),
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Mail send failed: ' . $e->getMessage());
                    }
                }
            }
        });
    }

    /**
     * Create a newly registered individual payroll record.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Payroll The newly created Payroll model instance.
     */
    public function createPayroll(array $data): Payroll
    {
        return DB::transaction(function () use ($data) {
            $data['user_id'] = Auth::id();
            $data['reference_no'] = $data['reference_no'] ?? 'payroll-' . date('Ymd') . '-' . date('his');

            if (!empty($data['created_at'])) {
                $data['created_at'] = Carbon::parse(str_replace("/", "-", $data['created_at']))->format('Y-m-d H:i:s');
            } else {
                $data['created_at'] = now();
            }

            $salary = (float) ($data['salary_amount'] ?? 0);
            $previous = (float) ($data['expense'] ?? 0);
            $commissionInput = (float) ($data['commission'] ?? 0);
            $isAgent = $data['is_agent'] ?? false;
            $percent = (float) ($data['commission_percent'] ?? 0);

            $commission = $commissionInput;
            if ($isAgent && $percent > 0) {
                $commission = ($salary * $percent) / 100;
            }

            $data['amount_array'] = [
                'salary' => $salary,
                'commission' => $commission,
                'previous' => $previous,
                'total' => (float) ($data['amount'] ?? 0),
            ];

            // Safely retrieve status value
            $data['status'] = $data['status'] instanceof PayrollStatusEnum ? $data['status']->value : $data['status'];

            $payroll = Payroll::query()->create($data);

            $employee = Employee::query()->find($data['employee_id']);
            if ($employee && $employee->email) {
                $mailSetting = MailSetting::latest()->first();
                if ($mailSetting) {
                    $this->setMailInfo($mailSetting);
                    try {
                        Mail::to($employee->email)->queue(new PayrollDetails([
                            'reference_no' => $payroll->reference_no,
                            'amount' => $payroll->amount,
                            'name' => $employee->name,
                            'email' => $employee->email,
                            'currency' => config('currency', 'USD'),
                        ]));
                    } catch (\Exception $e) {
                        Log::error('Payroll creation mail send failed: ' . $e->getMessage());
                    }
                }
            }

            return $payroll;
        });
    }

    /**
     * Update an existing payroll record.
     *
     * @param  Payroll  $payroll  The payroll model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Payroll The freshly updated Payroll model instance.
     */
    public function updatePayroll(Payroll $payroll, array $data): Payroll
    {
        return DB::transaction(function () use ($payroll, $data) {
            if (!empty($data['created_at'])) {
                $data['created_at'] = Carbon::parse(str_replace("/", "-", $data['created_at']))->format('Y-m-d H:i:s');
            }

            $amountArray = $payroll->amount_array ?? [];

            $salary = (float) ($data['salary_amount'] ?? ($amountArray['salary'] ?? 0));
            $previous = (float) ($data['expense'] ?? ($amountArray['previous'] ?? $amountArray['expense'] ?? 0));
            $commissionInput = (float) ($data['commission'] ?? ($amountArray['commission'] ?? 0));
            $isAgent = $data['is_agent'] ?? false;
            $percent = (float) ($data['commission_percent'] ?? 0);

            $commission = $commissionInput;
            if ($isAgent && $percent > 0) {
                $commission = ($salary * $percent) / 100;
            }

            $total = (float) ($data['amount'] ?? $payroll->amount);

            $data['amount_array'] = [
                'salary' => $salary,
                'commission' => $commission,
                'previous' => $previous,
                'total' => $total,
            ];

            $data['amount'] = $total;

            // Safely retrieve status value
            if (isset($data['status'])) {
                $data['status'] = $data['status'] instanceof PayrollStatusEnum ? $data['status']->value : $data['status'];
            }

            $payroll->update($data);

            return $payroll->fresh(['employee', 'user', 'account']);
        });
    }

    /**
     * Delete a payroll record.
     *
     * @param  Payroll  $payroll
     * @return void
     */
    public function deletePayroll(Payroll $payroll): void
    {
        DB::transaction(function () use ($payroll) {
            $payroll->delete();
        });
    }

    /**
     * Bulk delete multiple payroll records.
     *
     * @param  array<int>  $ids  Array of payroll IDs to be deleted.
     * @return int The total count of successfully deleted payroll records.
     */
    public function bulkDeletePayrolls(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Payroll::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Update the status for multiple payroll records.
     *
     * @param  array<int>  $ids  Array of payroll IDs to update.
     * @param  string  $status  The new status value.
     * @return int The number of records updated.
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        return Payroll::query()->whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Import multiple payroll records from an uploaded file.
     *
     * @param  UploadedFile  $file  The uploaded spreadsheet file.
     * @return void
     */
    public function importPayrolls(UploadedFile $file): void
    {
        ExcelFacade::import(new PayrollsImport, $file);
    }

    /**
     * Download a payrolls CSV template.
     *
     * @return string The absolute path to the downloaded file.
     * @throws RuntimeException If the template file is missing.
     */
    public function download(): string
    {
        $fileName = 'payrolls-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template payrolls not found.');
        }
        return $path;
    }

    /**
     * Generate an export file containing payroll data.
     *
     * @param  array<int>  $ids  Specific payroll IDs to export.
     * @param  string  $format  The file format requested (excel/pdf).
     * @param  array<string>  $columns  Specific column names to include.
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters.
     * @return string The relative file path to the generated export file.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'payrolls_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new PayrollsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}
