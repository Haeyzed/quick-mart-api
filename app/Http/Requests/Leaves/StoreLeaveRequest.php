<?php

declare(strict_types=1);

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreLeaveRequest
 *
 * Handles validation and authorization for creating a new leave request.
 */
class StoreLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * The ID of the employee requesting leave.
             *
             * @example 5
             */
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')],

            /**
             * The ID of the requested leave type.
             *
             * @example 2
             */
            'leave_types' => ['required', 'integer', Rule::exists('leave_types', 'id')],

            /**
             * The starting date of the leave.
             *
             * @example 2024-12-01
             */
            'start_date' => ['required', 'date'],

            /**
             * The ending date of the leave. Must be after or equal to the start date.
             *
             * @example 2024-12-05
             */
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
