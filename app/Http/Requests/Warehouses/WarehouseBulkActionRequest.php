<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class WarehouseBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions (like deletion or status updates) on multiple warehouses.
 */
class WarehouseBulkActionRequest extends FormRequest
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
             * An array of valid warehouse IDs to perform the bulk action on.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single warehouse ID ensuring it exists in the database (excluding trashed).
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('warehouses', 'id')->withoutTrashed()],
        ];
    }
}
