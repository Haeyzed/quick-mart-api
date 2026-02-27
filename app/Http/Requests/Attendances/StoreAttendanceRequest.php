<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendances;

use App\Enums\AttendanceStatusEnum;
use App\Http\Requests\BaseRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class StoreAttendanceRequest
 *
 * Handles validation and authorization for creating a new attendance record.
 */
class StoreAttendanceRequest extends BaseRequest
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
             * The date of the attendance record.
             *
             * @example 2024-12-01
             */
            'date' => ['required', 'date'],

            /**
             * An array of employee IDs to apply this attendance record to.
             *
             * @example [1, 5, 12]
             */
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],

            /**
             * The check-in time (Format: HH:MM or HH:MM:SS).
             *
             * @example 08:00:00
             */
            'checkin' => ['required', 'string'],

            /**
             * The check-out time (Format: HH:MM or HH:MM:SS).
             *
             * @example 17:00:00
             */
            'checkout' => ['nullable', 'string'],

            /**
             * Any notes regarding this attendance.
             *
             * @example Traffic delay.
             */
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
