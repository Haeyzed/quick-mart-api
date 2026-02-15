<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReparentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var \App\Models\Category|null $category */
        $category = $this->route('category');

        return [
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
