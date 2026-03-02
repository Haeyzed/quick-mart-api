<?php

declare(strict_types=1);

namespace App\Http\Requests\JobOpenings;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreJobOpeningRequest
 *
 * Handles validation and authorization for creating a new job opening.
 */
class StoreJobOpeningRequest extends BaseRequest
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
             * The title of the job opening.
             *
             * @example "Senior PHP Developer"
             */
            'title' => ['required', 'string', 'max:255'],
            /**
             * The ID of the department.
             *
             * @example 2
             */
            'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')],
            /**
             * The ID of the designation.
             *
             * @example 3
             */
            'designation_id' => ['nullable', 'integer', Rule::exists('designations', 'id')],
            /**
             * The status of the job opening (draft, open, closed).
             *
             * @example "open"
             */
            'status' => ['nullable', 'string', 'in:draft,open,closed'],
            /**
             * The job description.
             *
             * @example "We are looking for..."
             */
            'description' => ['nullable', 'string'],
            /**
             * The number of openings.
             *
             * @example 2
             */
            'openings_count' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
