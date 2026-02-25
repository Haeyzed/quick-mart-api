<?php

declare(strict_types=1);

namespace App\Http\Requests\Attendances;

use App\Enums\AttendanceStatusEnum;
use App\Models\Attendance;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Class UpdateAttendanceRequest
 *
 * Handles validation and authorization for updating an existing attendance record.
 */
class UpdateAttendanceRequest extends FormRequest
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
        /** @var Attendance|null $attendance */
        $attendance = $this->route('attendance');

        return [
            /**
             * The date of the attendance record.
             *
             * @example 2024-12-01
             */
            'date' => ['sometimes', 'required', 'date'],

            /**
             * The check-in time (Format: HH:MM or HH:MM:SS).
             *
             * @example 08:00:00
             */
            'checkin' => ['sometimes', 'required', 'string'],

            /**
             * The check-out time (Format: HH:MM or HH:MM:SS).
             *
             * @example 17:00:00
             */
            'checkout' => ['nullable', 'string'],

            /**
             * Status flag (present, late, absent).
             *
             * @example late
             */
            'status' => ['nullable', new Enum(AttendanceStatusEnum::class)],

            /**
             * Any notes regarding this attendance.
             *
             * @example Checked out early for medical appointment.
             */
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
