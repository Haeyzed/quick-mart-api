<?php

declare(strict_types=1);

namespace App\Http\Requests\Leaves;

use App\Enums\LeaveStatusEnum;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateLeaveRequest
 *
 * Handles validation and authorization for updating an existing leave record.
 */
class UpdateLeaveRequest extends BaseRequest
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
             * The ID of the employee.
             *
             * @example 5
             */
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')],

            /**
             * The ID of the leave type.
             *
             * @example 2
             */
            'leave_types' => ['sometimes', 'required', 'integer', Rule::exists('leave_types', 'id')],

            /**
             * The starting date of the leave.
             *
             * @example 2024-12-01
             */
            'start_date' => ['sometimes', 'required', 'date'],

            /**
             * The ending date of the leave.
             *
             * @example 2024-12-05
             */
            'end_date' => ['sometimes', 'required', 'date', 'after_or_equal:start_date'],

            /**
             * The approval status of the leave.
             *
             * @example Approved
             */
            'status' => ['sometimes', 'required', 'string', new Enum(LeaveStatusEnum::class)],
        ];
    }
}
