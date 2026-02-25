<?php

declare(strict_types=1);

namespace App\Http\Requests\Shifts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreShiftRequest
 *
 * Handles validation and authorization for creating a new shift.
 */
class StoreShiftRequest extends FormRequest
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
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
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
             * The unique name of the shift.
             *
             * @example Morning Shift
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name')->withoutTrashed(),
            ],

            /**
             * The time the shift starts (Format: HH:MM).
             *
             * @example 08:00
             */
            'start_time' => ['required', 'date_format:H:i'],

            /**
             * The time the shift ends (Format: HH:MM).
             *
             * @example 16:00
             */
            'end_time' => ['required', 'date_format:H:i'],

            /**
             * The grace period for checking in late, in minutes.
             *
             * @example 15
             */
            'grace_in' => ['required', 'integer', 'min:0'],

            /**
             * The grace period for checking out early, in minutes.
             *
             * @example 10
             */
            'grace_out' => ['required', 'integer', 'min:0'],

            /**
             * The total required working hours for this shift.
             *
             * @example 8.0
             */
            'total_hours' => ['required', 'numeric', 'min:0'],

            /**
             * Indicates whether the shift is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
