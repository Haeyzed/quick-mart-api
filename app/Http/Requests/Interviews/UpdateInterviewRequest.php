<?php

declare(strict_types=1);

namespace App\Http\Requests\Interviews;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateInterviewRequest
 *
 * Handles validation and authorization for updating an existing interview.
 */
class UpdateInterviewRequest extends BaseRequest
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
             * The date and time when the interview is scheduled.
             *
             * @example "2024-02-15 14:00:00"
             */
            'scheduled_at' => ['sometimes', 'required', 'date'],

            /**
             * The duration of the interview in minutes.
             *
             * @example 60
             */
            'duration_minutes' => ['nullable', 'integer', 'min:1'],

            /**
             * The ID of the user conducting the interview.
             *
             * @example 1
             */
            'interviewer_id' => ['nullable', 'integer', Rule::exists('users', 'id')],

            /**
             * Post-interview feedback notes.
             *
             * @example "Strong technical skills"
             */
            'feedback' => ['nullable', 'string'],

            /**
             * The interview status (scheduled, completed, cancelled).
             *
             * @example "completed"
             */
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled'],
        ];
    }
}
