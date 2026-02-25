<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use App\Models\Leave;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

/**
 * Class PayrollResource
 *
 * @mixin Payroll
 */
class PayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employeeId = $this->employee_id;

        // Dynamically compute legacy statistics
        $leavesCount = Leave::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'Approved')
            ->sum('days');

        $attendanceDays = Attendance::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'Present')
            ->count();

        $workDurationSeconds = Attendance::query()
            ->where('employee_id', $employeeId)
            ->where('status', 'Present')
            ->sum(DB::raw('TIME_TO_SEC(TIMEDIFF(checkout, checkin))'));

        $workDurationHours = round((float)$workDurationSeconds / 3600, 2);

        return [
            /**
             * The unique identifier for the payroll record.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The unique reference number.
             *
             * @example PR-123456
             */
            'reference_no' => $this->reference_no,

            /**
             * The ID of the employee.
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
             * The ID of the account.
             *
             * @example 2
             */
            'account_id' => $this->account_id,

            /**
             * The name of the account (if relation is loaded).
             *
             * @example Main Bank Account
             */
            'account_name' => $this->whenLoaded('account', fn() => $this->account->name),

            /**
             * The total amount.
             *
             * @example 1500.50
             */
            'amount' => (float) $this->amount,

            /**
             * Detailed array breakdown of the payroll amount.
             *
             * @example {"basic": 1000, "overtime": 500}
             */
            'amount_array' => $this->amount_array,

            /**
             * The paying method.
             *
             * @example Bank Transfer
             */
            'paying_method' => $this->paying_method,

            /**
             * The month of the payroll.
             *
             * @example 2024-12
             */
            'month' => $this->month,

            /**
             * Status of the payroll.
             *
             * @example paid
             */
            'status' => $this->status?->value,

            /**
             * Notes regarding the payroll.
             *
             * @example Holiday bonus included.
             */
            'note' => $this->note,

            /**
             * Dynamically calculated statistics for the specific employee.
             */
            'statistics' => [
                'approved_leaves' => (float) $leavesCount,
                'attendance_days' => $attendanceDays,
                'work_duration_hours' => $workDurationHours,
            ],

            /**
             * The date and time when the record was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the record was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
