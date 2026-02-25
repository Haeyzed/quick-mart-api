<?php

declare(strict_types=1);

namespace App\Http\Requests\Overtimes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class OvertimeBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on overtime requests.
 */
class OvertimeBulkActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
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
             * An array of valid overtime IDs.
             *
             * @example [10, 11, 12]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single overtime ID ensuring it exists in the database.
             *
             * @example 10
             */
            'ids.*' => ['required', 'integer', Rule::exists('overtimes', 'id')->withoutTrashed()],
        ];
    }
}
