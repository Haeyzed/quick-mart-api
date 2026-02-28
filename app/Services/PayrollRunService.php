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

class PayrollRunService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = PayrollRun::query()->with('generatedByUser')->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['year'])) {
            $query->where('year', (int) $filters['year']);
        }
        if (! empty($filters['month'])) {
            $query->where('month', $filters['month']);
        }

        return $query->paginate($perPage);
    }

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

    public function create(array $data): PayrollRun
    {
        return DB::transaction(function () use ($data) {
            $data['generated_by'] = Auth::id();

            return PayrollRun::query()->create($data);
        });
    }

    public function update(PayrollRun $payrollRun, array $data): PayrollRun
    {
        $payrollRun->update($data);

        return $payrollRun->fresh();
    }

    public function delete(PayrollRun $payrollRun): void
    {
        DB::transaction(function () use ($payrollRun) {
            $payrollRun->entries()->each(fn (PayrollEntry $e) => $e->items()->delete());
            $payrollRun->entries()->delete();
            $payrollRun->delete();
        });
    }

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
