<?php

declare(strict_types=1);

namespace App\Http\Requests\Holidays;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateHolidayRequest
 *
 * Handles validation and authorization for updating an existing holiday.
 */
class UpdateHolidayRequest extends FormRequest
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
             * The user ID the holiday belongs to.
             *
             * @example 1
             */
            'user_id' => ['sometimes', 'nullable', 'integer', Rule::exists('users', 'id')],

            /**
             * Start date of the holiday period.
             *
             * @example 2024-12-25
             */
            'from_date' => ['sometimes', 'required', 'date'],

            /**
             * End date of the holiday period. Must be on or after from_date.
             *
             * @example 2024-12-31
             */
            'to_date' => ['sometimes', 'required', 'date', 'after_or_equal:from_date'],

            /**
             * Optional note or reason for the holiday.
             *
             * @example Annual leave
             */
            'note' => ['nullable', 'string', 'max:500'],

            /**
             * Whether the holiday is approved.
             *
             * @example true
             */
            'is_approved' => ['nullable', 'boolean'],

            /**
             * Whether the holiday recurs.
             *
             * @example false
             */
            'recurring' => ['nullable', 'boolean'],

            /**
             * Optional region or location for the holiday.
             *
             * @example HQ
             */
            'region' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * Useful for casting types or manipulating the request payload before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_approved')) {
            $this->merge(['is_approved' => filter_var($this->is_approved, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('recurring')) {
            $this->merge(['recurring' => filter_var($this->recurring, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->filled('from_date')) {
            $this->merge(['from_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->from_date)))]);
        }
        if ($this->filled('to_date')) {
            $this->merge(['to_date' => date('Y-m-d', strtotime(str_replace('/', '-', $this->to_date)))]);
        }
    }
}
