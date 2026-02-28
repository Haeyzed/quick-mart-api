<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PayrollEntry;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PayrollEntry
 */
class PayrollEntryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payroll_run_id' => $this->payroll_run_id,
            'employee_id' => $this->employee_id,
            'gross_salary' => (float) $this->gross_salary,
            'total_deductions' => (float) $this->total_deductions,
            'net_salary' => (float) $this->net_salary,
            'status' => $this->status,
            'employee' => $this->whenLoaded('employee', fn () => [
                'id' => $this->employee->id,
                'name' => $this->employee->name,
                'employee_code' => $this->employee->employee_code,
            ]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'salary_component_id' => $i->salary_component_id,
                'amount' => (float) $i->amount,
                'salary_component' => $i->relationLoaded('salaryComponent') ? [
                    'id' => $i->salaryComponent->id,
                    'name' => $i->salaryComponent->name,
                    'type' => $i->salaryComponent->type,
                ] : null,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
