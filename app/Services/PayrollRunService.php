<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class PayrollRunService
 *
 * Handles all core business logic and database interactions for Payroll Runs.
 * Acts as the intermediary between the controllers and the database layer.
 */
class PayrollRunService
{
    /**
     * Get paginated payroll runs based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return PayrollRun::query()
            ->with('generatedByUser')
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get payroll run options for dropdowns (id, month/year label).
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return PayrollRun::query()
            ->select('id', 'month', 'year')
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->limit(24)
            ->get()
            ->map(fn (PayrollRun $run) => [
                'value' => $run->id,
                'label' => $run->month.' / '.$run->year,
            ]);
    }

    /**
     * Create a newly registered payroll run.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return PayrollRun The newly created PayrollRun model instance.
     */
    public function create(array $data): PayrollRun
    {
        return DB::transaction(function () use ($data) {
            $data['generated_by'] = Auth::id();

            return PayrollRun::query()->create($data);
        });
    }

    /**
     * Update an existing payroll run.
     *
     * @param  PayrollRun  $payrollRun  The payroll run model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return PayrollRun The freshly updated PayrollRun model instance.
     */
    public function update(PayrollRun $payrollRun, array $data): PayrollRun
    {
        $payrollRun->update($data);

        return $payrollRun->fresh();
    }

    /**
     * Delete a payroll run and its entries and entry items.
     */
    public function delete(PayrollRun $payrollRun): void
    {
        DB::transaction(function () use ($payrollRun) {
            $payrollRun->entries()->each(fn (PayrollEntry $e) => $e->items()->delete());
            $payrollRun->entries()->delete();
            $payrollRun->delete();
        });
    }

    /**
     * Generate payroll entries for all active employees for the given run.
     *
     * @param  PayrollRun  $payrollRun  The payroll run to generate entries for.
     * @return PayrollRun The payroll run with entries loaded.
     */
    public function generateEntries(PayrollRun $payrollRun): PayrollRun
    {
        return DB::transaction(function () use ($payrollRun) {
            $payrollRun->entries()->delete();

            $employees = Employee::query()
                ->with(['salaryStructure.structureItems.salaryComponent'])
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->whereNull('employment_status')->orWhere('employment_status', 'active');
                })
                ->get();

            foreach ($employees as $employee) {
                $gross = (float) $employee->basic_salary;
                $deductions = 0.0;

                if ($employee->salaryStructure && $employee->salaryStructure->structureItems->isNotEmpty()) {
                    $gross = 0.0;
                    foreach ($employee->salaryStructure->structureItems as $item) {
                        $comp = $item->salaryComponent;
                        if (! $comp) {
                            continue;
                        }
                        if ($comp->type === 'earning') {
                            $gross += (float) $item->amount;
                        } else {
                            $deductions += (float) $item->amount;
                        }
                    }
                }

                PayrollEntry::query()->create([
                    'payroll_run_id' => $payrollRun->id,
                    'employee_id' => $employee->id,
                    'gross_salary' => $gross,
                    'total_deductions' => $deductions,
                    'net_salary' => $gross - $deductions,
                    'status' => 'draft',
                ]);
            }

            return $payrollRun->fresh(['entries.employee']);
        });
    }
}
