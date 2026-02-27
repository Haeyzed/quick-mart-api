<?php

declare(strict_types=1);

namespace App\Http\Requests\Overtimes;

use App\Enums\OvertimeStatusEnum;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateOvertimeRequest
 *
 * Handles validation and authorization for updating an existing overtime record.
 */
class UpdateOvertimeRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
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
             * The ID of the employee claiming overtime.
             *
             * @example 5
             */
            'employee_id' => ['sometimes', 'required', 'integer', Rule::exists('employees', 'id')],

            /**
             * The date the overtime was performed.
             *
             * @example 2024-12-01
             */
            'date' => ['sometimes', 'required', 'date'],

            /**
             * The total hours worked as overtime.
             *
             * @example 5.0
             */
            'hours' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * The hourly rate for the overtime.
             *
             * @example 15.50
             */
            'rate' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * The approval status of the overtime request.
             *
             * @example Approved
             */
            'status' => ['sometimes', 'required', new Enum(OvertimeStatusEnum::class)],
        ];
    }
}
