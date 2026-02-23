<?php

declare(strict_types=1);

namespace App\Http\Requests\Leaves;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class LeaveBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on leaves.
 */
class LeaveBulkActionRequest extends FormRequest
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
             * An array of valid leave IDs.
             *
             * @example [10, 11, 12]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single leave ID ensuring it exists in the database.
             *
             * @example 10
             */
            'ids.*' => ['required', 'integer', Rule::exists('leaves', 'id')->withoutTrashed()],
        ];
    }
}
