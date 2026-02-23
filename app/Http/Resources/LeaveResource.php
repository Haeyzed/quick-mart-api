<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Leave
 */
class LeaveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the leave request.
             *
             * @example 15
             */
            'id' => $this->id,

            /**
             * The ID of the employee who requested the leave.
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
             * The ID of the requested leave type.
             *
             * @example 2
             */
            'leave_type_id' => $this->leave_types,

            /**
             * The name of the leave type (if relation is loaded).
             *
             * @example Sick Leave
             */
            'leave_type_name' => $this->whenLoaded('leaveType', fn() => $this->leaveType->name),

            /**
             * The start date of the leave.
             *
             * @example 2024-12-01
             */
            'start_date' => $this->start_date?->format('Y-m-d'),

            /**
             * The end date of the leave.
             *
             * @example 2024-12-05
             */
            'end_date' => $this->end_date?->format('Y-m-d'),

            /**
             * The total calculated duration of the leave in days.
             *
             * @example 5.0
             */
            'days' => (float) $this->days,

            /**
             * The current status of the leave request.
             *
             * @example Pending
             */
            'status' => $this->status,

            /**
             * The ID of the user who approved or rejected the leave.
             *
             * @example 1
             */
            'approver_id' => $this->approver_id,

            /**
             * The name of the approver (if relation is loaded).
             *
             * @example Admin User
             */
            'approver_name' => $this->whenLoaded('approver', fn() => $this->approver->name),

            /**
             * The date and time when the leave request was created.
             *
             * @example 2024-11-20T08:30:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the leave request was last updated.
             *
             * @example 2024-11-21T09:15:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
