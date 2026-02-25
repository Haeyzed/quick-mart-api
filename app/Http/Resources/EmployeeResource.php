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
             * The ID of the associated department.
             *
             * @example 2
             */
            'department_id' => $this->department_id,

            /**
             * The loaded department relationship data.
             *
             * @example {"id": 2, "name": "IT"}
             */
            'department' => $this->whenLoaded('department', fn () => ['id' => $this->department->id, 'name' => $this->department->name]),

            /**
             * The ID of the associated designation.
             *
             * @example 3
             */
            'designation_id' => $this->designation_id,

            /**
             * The loaded designation relationship data.
             *
             * @example {"id": 3, "name": "Senior Developer"}
             */
            'designation' => $this->whenLoaded('designation', fn () => ['id' => $this->designation->id, 'name' => $this->designation->name]),

            /**
             * The ID of the associated shift.
             *
             * @example 1
             */
            'shift_id' => $this->shift_id,

            /**
             * The street address of the employee.
             *
             * @example 123 Main Street
             */
            'address' => $this->address,

            /**
             * The ID of the associated country.
             *
             * @example 1
             */
            'country_id' => $this->country_id,

            /**
             * The ID of the associated state.
             *
             * @example 12
             */
            'state_id' => $this->state_id,

            /**
             * The ID of the associated city.
             *
             * @example 45
             */
            'city_id' => $this->city_id,

            /**
             * The loaded country relationship data.
             *
             * @example {"id": 1, "name": "United States"}
             */
            'country' => $this->whenLoaded('country', fn () => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null),

            /**
             * The loaded state relationship data.
             *
             * @example {"id": 12, "name": "California"}
             */
            'state' => $this->whenLoaded('state', fn () => $this->state ? ['id' => $this->state->id, 'name' => $this->state->name] : null),

            /**
             * The loaded city relationship data.
             *
             * @example {"id": 45, "name": "Los Angeles"}
             */
            'city' => $this->whenLoaded('city', fn () => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null),

            /**
             * The relative path to the employee's image.
             *
             * @example images/employees/avatar.png
             */
            'image' => $this->image,

            /**
             * The absolute URL to the employee's image.
             *
             * @example https://api.example.com/storage/images/employees/avatar.png
             */
            'image_url' => $this->image_url,

            /**
             * Indicates if the employee is currently active.
             *
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Indicates if the employee is a sales agent.
             *
             * @example false
             */
            'is_sale_agent' => $this->is_sale_agent,

            /**
             * The commission percentage for the sale agent.
             *
             * @example 5.5
             */
            'sale_commission_percent' => $this->sale_commission_percent,

            /**
             * The structured array defining sales targets and tier percentages.
             *
             * @example [{"sales_from": 0, "sales_to": 1000, "percent": 5}]
             */
            'sales_target' => $this->sales_target,

            /**
             * The human-readable active status.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

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
