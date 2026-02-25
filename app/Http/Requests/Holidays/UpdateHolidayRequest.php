<?php

declare(strict_types=1);

namespace App\Http\Requests\Holidays;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateHolidayRequest
 *
 * Handles validation and authorization for updating an existing holiday.
 */
class UpdateHolidayRequest extends FormRequest
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

        if ($this->has('recurring')) {
            $mergeData['recurring'] = filter_var($this->recurring, FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->has('is_approved')) {
            $mergeData['is_approved'] = filter_var($this->is_approved, FILTER_VALIDATE_BOOLEAN);
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
             * The starting date of the holiday.
             *
             * @example 2024-12-25
             */
            'from_date' => ['sometimes', 'required', 'date'],

            /**
             * The ending date of the holiday.
             *
             * @example 2024-12-26
             */
            'to_date' => ['sometimes', 'required', 'date', 'after_or_equal:from_date'],

            /**
             * Notes or reason for the holiday.
             *
             * @example Christmas Holiday
             */
            'note' => ['nullable', 'string', 'max:500'],

            /**
             * Indicates whether the holiday is recurring annually.
             *
             * @example true
             */
            'recurring' => ['nullable', 'boolean'],

            /**
             * Region the holiday applies to, if specific.
             *
             * @example Global
             */
            'region' => ['nullable', 'string', 'max:255'],

            /**
             * Approval status of the holiday.
             *
             * @example true
             */
            'is_approved' => ['nullable', 'boolean'],
        ];
    }
}
