<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class EmployeeResource
 *
 * @mixin Employee
 */
class EmployeeResource extends JsonResource
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
             * The unique identifier for the employee.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The staff identifier or code.
             *
             * @example EMP-001
             */
            'staff_id' => $this->staff_id,

            /**
             * The full name of the employee.
             *
             * @example Jane Doe
             */
            'name' => $this->name,

            /**
             * The email address of the employee.
             *
             * @example janedoe@example.com
             */
            'email' => $this->email,

            /**
             * The phone number of the employee.
             *
             * @example +1234567890
             */
            'phone_number' => $this->phone_number,

            /**
             * The basic salary of the employee.
             *
             * @example 5000.00
             */
            'basic_salary' => (float) $this->basic_salary,

            /**
             * The physical address of the employee.
             *
             * @example 123 Main Street
             */
            'address' => $this->address,

            /**
             * The associated country ID.
             *
             * @example 1
             */
            'country_id' => $this->country_id,

            /**
             * The associated state ID.
             *
             * @example 12
             */
            'state_id' => $this->state_id,

            /**
             * The associated city ID.
             *
             * @example 45
             */
            'city_id' => $this->city_id,

            /**
             * The URL of the employee's image.
             *
             * @example "https://yourdomain.com/storage/images/employees/avatar.png"
             */
            'image_url' => $this->image_url,

            /**
             * Details regarding the user account associated with the employee.
             */
            'user' => $this->whenLoaded('user', fn() => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone_number' => $this->user->phone_number,
                'is_active' => $this->user->is_active,
                'roles' => $this->user->roles->pluck('id'),
                'permissions' => $this->user->permissions->pluck('id'),
            ]),

            /**
             * The department associated with the employee.
             */
            'department' => $this->whenLoaded('department', fn() => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),

            /**
             * The designation associated with the employee.
             */
            'designation' => $this->whenLoaded('designation', fn() => [
                'id' => $this->designation->id,
                'name' => $this->designation->name,
            ]),

            /**
             * The shift associated with the employee.
             */
            'shift' => $this->whenLoaded('shift', fn() => [
                'id' => $this->shift->id,
                'name' => $this->shift->name,
                'start_time' => $this->shift->start_time,
                'end_time' => $this->shift->end_time,
            ]),

            /**
             * Indicates if the employee is active.
             *
             * @example true
             */
            'is_active' => (bool) $this->is_active,

            /**
             * Indicates if the employee is a sales agent.
             *
             * @example false
             */
            'is_sale_agent' => (bool) $this->is_sale_agent,

            /**
             * The commission percentage for the sale agent.
             *
             * @example 5.5
             */
            'sale_commission_percent' => $this->sale_commission_percent ? (float) $this->sale_commission_percent : null,

            /**
             * The structured array defining sales targets and tier percentages.
             * Ensures proper float casting for frontend mathematical calculations.
             *
             * @example [{"sales_from": 0, "sales_to": 1000, "percent": 5}]
             */
            'sales_target' => is_array($this->sales_target) ? collect($this->sales_target)->map(fn($target) => [
                'sales_from' => isset($target['sales_from']) ? (float) $target['sales_from'] : 0.0,
                'sales_to'   => isset($target['sales_to']) ? (float) $target['sales_to'] : 0.0,
                'percent'    => isset($target['percent']) ? (float) $target['percent'] : 0.0,
            ])->toArray() : [],

            /**
             * The human-readable active status.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The human-readable sales agent status.
             *
             * @example yes
             */
            'sales_agent' => $this->is_sale_agent ? 'yes' : 'no',

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
