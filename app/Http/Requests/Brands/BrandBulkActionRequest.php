<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class BrandBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions (like deletion or status updates) on multiple brands.
 */
class BrandBulkActionRequest extends FormRequest
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
             * An array of valid brand IDs to perform the bulk action on.
             * @example [1, 2, 3]
             */
            'ids'   => ['required', 'array', 'min:1'],

            /**
             * A single brand ID ensuring it exists in the database.
             * @example 1
             */
            'ids.*' => ['required', 'integer', 'exists:brands,id'],
        ];
    }
}
