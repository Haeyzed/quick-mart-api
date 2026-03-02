<?php

declare(strict_types=1);

namespace App\Http\Requests\Interviews;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreInterviewRequest
 *
 * Handles validation and authorization for creating a new interview.
 */
class StoreInterviewRequest extends BaseRequest
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
             * The ID of the candidate being interviewed.
             *
             * @example 5
             */
            'candidate_id' => ['required', 'integer', Rule::exists('candidates', 'id')],

            /**
             * The date and time when the interview is scheduled.
             *
             * @example "2024-02-15 14:00:00"
             */
            'scheduled_at' => ['required', 'date'],

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
             * @example "scheduled"
             */
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled'],
        ];
    }
}
