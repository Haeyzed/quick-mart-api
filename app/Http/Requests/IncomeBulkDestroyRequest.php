<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * IncomeBulkDestroyRequest
 *
 * Validates incoming data for bulk deleting incomes.
 */
class IncomeBulkDestroyRequest extends FormRequest
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
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            /**
             * Array of income IDs to delete.
             *
             * @var array<int> @ids
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],
            /**
             * Individual income ID in the ids array.
             *
             * @var int @ids.*
             * @example 1
             */
            'ids.*' => ['required', 'integer', 'exists:incomes,id'],
        ];
    }
}

