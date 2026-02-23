<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaveTypes;

use App\Models\LeaveType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateLeaveTypeRequest
 *
 * Handles validation and authorization for updating an existing leave type.
 */
class UpdateLeaveTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $mergeData = [];

        if ($this->has('is_active')) {
            $mergeData['is_active'] = filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->has('encashable')) {
            $mergeData['encashable'] = filter_var($this->encashable, FILTER_VALIDATE_BOOLEAN);
        }

        if (!empty($mergeData)) {
            $this->merge($mergeData);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var LeaveType|null $leaveType */
        $leaveType = $this->route('leave_type');

        return [
            /**
             * The name of the leave type. Must be unique excluding the current record.
             *
             * @example Sick Leave
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types', 'name')->ignore($leaveType)->withoutTrashed(),
            ],

            /**
             * The maximum number of days allowed per year.
             *
             * @example 14
             */
            'annual_quota' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * Indicates if the leave days can be encashed.
             *
             * @example false
             */
            'encashable' => ['sometimes', 'required', 'boolean'],

            /**
             * Carry forward limit for the next year.
             *
             * @example 0
             */
            'carry_forward_limit' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * Indicates whether the leave type is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
