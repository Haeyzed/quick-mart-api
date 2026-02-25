<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Overtime;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Overtime
 */
class OvertimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the overtime request.
             *
             * @example 15
             */
            'id' => $this->id,

            /**
             * The ID of the employee who performed the overtime.
             *
             * @example 5
             */
            'employee_id' => $this->employee_id,

            /**
             * The name of the employee (if relation is loaded).
             *
             * @example John Doe
             */
            'employee_name' => $this->whenLoaded('employee', fn() => $this->employee->name),

            /**
             * The date the overtime was performed.
             *
             * @example 2024-12-01
             */
            'date' => $this->date?->format('Y-m-d'),

            /**
             * The total hours worked as overtime.
             *
             * @example 4.5
             */
            'hours' => (float) $this->hours,

            /**
             * The hourly rate for the overtime.
             *
             * @example 15.50
             */
            'rate' => (float) $this->rate,

            /**
             * The calculated monetary value of the overtime (Hours * Rate).
             *
             * @example 69.75
             */
            'amount' => (float) $this->amount,

            /**
             * The current status of the overtime request.
             *
             * @example Pending
             */
            'status' => $this->status,

            /**
             * The ID of the user who approved or rejected the overtime.
             *
             * @example 1
             */
            'approved_by' => $this->approved_by,

            /**
             * The name of the approver (if relation is loaded).
             *
             * @example Admin User
             */
            'approver_name' => $this->whenLoaded('approver', fn() => $this->approver->name),

            /**
             * The date and time when the overtime request was created.
             *
             * @example 2024-11-20T08:30:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the overtime request was last updated.
             *
             * @example 2024-11-21T09:15:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
