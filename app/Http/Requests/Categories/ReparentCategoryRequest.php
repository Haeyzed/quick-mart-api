<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class ReparentCategoryRequest
 *
 * Handles validation and authorization for reparenting a category (updating its parent_id).
 */
class ReparentCategoryRequest extends FormRequest
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
        /** @var \App\Models\Category|null $category */
        $category = $this->route('category');

        return [
            /**
             * The new parent category ID. Null to move to root. Cannot be self.
             *
             * @example 2
             */
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNot('id', $category?->id)
                    ->withoutTrashed(),
            ],
        ];
    }
}
