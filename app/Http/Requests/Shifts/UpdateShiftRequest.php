<?php

declare(strict_types=1);

namespace App\Http\Requests\Shifts;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateShiftRequest
 *
 * Handles validation and authorization for updating an existing shift.
 */
class UpdateShiftRequest extends FormRequest
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
        /** @var Shift|null $shift */
        $shift = $this->route('shift');

        return [
            /**
             * The unique name of the shift.
             *
             * @example Evening Shift
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('shifts', 'name')->ignore($shift)->withoutTrashed(),
            ],

            /**
             * The time the shift starts (Format: HH:MM).
             *
             * @example 16:00
             */
            'start_time' => ['sometimes', 'required', 'date_format:H:i'],

            /**
             * The time the shift ends (Format: HH:MM).
             *
             * @example 00:00
             */
            'end_time' => ['sometimes', 'required', 'date_format:H:i'],

            /**
             * The grace period for checking in late, in minutes.
             *
             * @example 15
             */
            'grace_in' => ['sometimes', 'required', 'integer', 'min:0'],

            /**
             * The grace period for checking out early, in minutes.
             *
             * @example 10
             */
            'grace_out' => ['sometimes', 'required', 'integer', 'min:0'],

            /**
             * The total required working hours for this shift.
             *
             * @example 8.0
             */
            'total_hours' => ['sometimes', 'required', 'numeric', 'min:0'],

            /**
             * Indicates whether the shift is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
