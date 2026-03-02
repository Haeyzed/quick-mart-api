<?php

declare(strict_types=1);

namespace App\Http\Requests\Interviews;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class InterviewBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on interviews.
 */
class InterviewBulkActionRequest extends BaseRequest
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
             * An array of valid interview IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single interview ID ensuring it exists in the database.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('interviews', 'id')],
        ];
    }
}
