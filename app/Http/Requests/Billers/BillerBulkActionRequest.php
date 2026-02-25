<?php

declare(strict_types=1);

namespace App\Http\Requests\Billers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class BillerBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on biller records.
 */
class BillerBulkActionRequest extends FormRequest
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
             * An array of valid biller IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single biller ID ensuring it exists in the database.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('billers', 'id')->withoutTrashed()],
        ];
    }
}
