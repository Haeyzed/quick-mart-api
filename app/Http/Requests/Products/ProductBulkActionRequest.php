<?php

declare(strict_types=1);

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ProductBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on multiple products.
 */
class ProductBulkActionRequest extends FormRequest
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
             * An array of valid product IDs to perform the bulk action on.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single product ID ensuring it exists in the database (excluding trashed).
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('products', 'id')->withoutTrashed()],
        ];
    }
}
