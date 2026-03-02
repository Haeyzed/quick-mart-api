<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PayrollEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class PayrollEntryService
 *
 * Handles all core business logic and database interactions for Payroll Entries.
 * Acts as the intermediary between the controllers and the database layer.
 */
class PayrollEntryService
{
    /**
     * Get paginated payroll entries for a given payroll run.
     *
     * @param  int  $payrollRunId  The payroll run ID to list entries for.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<PayrollEntry>
     */
    public function getPaginatedByRun(int $payrollRunId, int $perPage = 15): LengthAwarePaginator
    {
        return PayrollEntry::query()
            ->where('payroll_run_id', $payrollRunId)
            ->with(['employee', 'items.salaryComponent'])
            ->orderBy('id')
            ->paginate($perPage);
    }

    /**
     * Update an existing payroll entry.
     *
     * @param  PayrollEntry  $payrollEntry  The payroll entry model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return PayrollEntry The freshly updated PayrollEntry model instance.
     */
    public function update(PayrollEntry $payrollEntry, array $data): PayrollEntry
    {
        return DB::transaction(function () use ($payrollEntry, $data) {
            $payrollEntry->update($data);

            return $payrollEntry->fresh(['employee', 'items.salaryComponent']);
        });
    }
}
