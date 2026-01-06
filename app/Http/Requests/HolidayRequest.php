<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * HolidayRequest
 *
 * Validates incoming data for both creating and updating holidays.
 */
class HolidayRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        return [
            /**
             * The user ID who is requesting the holiday. If not provided, uses authenticated user.
             *
             * @var int|null @user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            /**
             * Start date of the holiday.
             *
             * @var string @from_date
             * @example 2024-01-01
             */
            'from_date' => ['required', 'date'],
            /**
             * End date of the holiday.
             *
             * @var string @to_date
             * @example 2024-01-05
             */
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            /**
             * Optional note about the holiday.
             *
             * @var string|null @note
             * @example Annual leave
             */
            'note' => ['nullable', 'string'],
            /**
             * Whether the holiday is approved.
             *
             * @var bool|null @is_approved
             * @example false
             */
            'is_approved' => ['nullable', 'boolean'],
            /**
             * Whether the holiday is recurring.
             *
             * @var bool|null @recurring
             * @example false
             */
            'recurring' => ['nullable', 'boolean'],
            /**
             * Region where the holiday applies.
             *
             * @var string|null @region
             * @example US
             */
            'region' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize date formats (convert "/" to "-" and format to Y-m-d)
        $fromDate = $this->from_date;
        $toDate = $this->to_date;

        if ($fromDate) {
            $fromDate = str_replace('/', '-', $fromDate);
            $fromDate = date('Y-m-d', strtotime($fromDate));
        }

        if ($toDate) {
            $toDate = str_replace('/', '-', $toDate);
            $toDate = date('Y-m-d', strtotime($toDate));
        }

        $this->merge([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'note' => $this->note ? trim($this->note) : null,
            'region' => $this->region ? trim($this->region) : null,
        ]);
    }

}

