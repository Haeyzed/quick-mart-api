<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PayrollEntry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PayrollEntryService
{
    /**
     * List entries for a payroll run.
     *
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

    public function update(PayrollEntry $payrollEntry, array $data): PayrollEntry
    {
        return DB::transaction(function () use ($payrollEntry, $data) {
            $payrollEntry->update($data);

            return $payrollEntry->fresh(['employee', 'items.salaryComponent']);
        });
    }
}
