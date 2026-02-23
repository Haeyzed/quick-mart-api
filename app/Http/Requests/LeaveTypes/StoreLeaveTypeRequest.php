<?php

declare(strict_types=1);

namespace App\Http\Requests\LeaveTypes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreLeaveTypeRequest
 *
 * Handles validation and authorization for creating a new leave type.
 */
class StoreLeaveTypeRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * Converts string boolean representations to actual booleans.
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
        return [
            /**
             * The unique name of the leave type.
             *
             * @example Annual Leave
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('leave_types', 'name')->withoutTrashed(),
            ],

            /**
             * The maximum number of days allowed per year for this leave type.
             *
             * @example 21
             */
            'annual_quota' => ['required', 'numeric', 'min:0'],

            /**
             * Indicates if the leave days can be converted to cash.
             *
             * @example true
             */
            'encashable' => ['required', 'boolean'],

            /**
             * The maximum number of unused days that can be carried to the next year.
             *
             * @example 5
             */
            'carry_forward_limit' => ['required', 'numeric', 'min:0'],

            /**
             * Indicates whether the leave type is active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
