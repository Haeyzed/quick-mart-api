<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Attendance
 */
class AttendanceResource extends JsonResource
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
             * The unique identifier for the attendance record.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The date of the attendance.
             *
             * @example 2024-12-01
             */
            'date' => $this->date?->format('Y-m-d'),

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
             * The ID of the user who recorded the attendance.
             *
             * @example 1
             */
            'user_id' => $this->user_id,

            /**
             * The name of the user who recorded the attendance (if relation is loaded).
             *
             * @example Admin User
             */
            'user_name' => $this->whenLoaded('user', fn() => $this->user->name),

            /**
             * The check-in time.
             *
             * @example 08:00:00
             */
            'checkin' => $this->checkin,

            /**
             * The check-out time.
             *
             * @example 17:00:00
             */
            'checkout' => $this->checkout,

            /**
             * Indicates the status (present, late, absent).
             *
             * @example present
             */
            'status' => $this->status?->value,

            /**
             * Notes regarding the attendance.
             *
             * @example Arrived late due to traffic.
             */
            'note' => $this->note,

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
