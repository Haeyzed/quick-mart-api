<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Form request for category create and update validation.
 *
 * Validates name, slug, short_description, page_title, image, icon, parent_id,
 * is_active, featured, is_sync_disable, and woocommerce_category_id.
 * Unique rules scope to non-soft-deleted categories only.
 */
class CategoryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True to allow (authorization handled by middleware/policy).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId)->whereNull('deleted_at'),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId)->whereNull('deleted_at'),
            ],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'icon'  => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'parent_id' => [
                'nullable', 'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($categoryId) {
                    if ($categoryId) {
                        $query->where('id', '!=', $categoryId);
                    }
                }),
            ],
            'is_active' => ['nullable', 'boolean'],
            'featured' => ['nullable', 'boolean'],
            'is_sync_disable' => ['nullable', 'boolean'],
            'woocommerce_category_id' => [
                'nullable',
                'integer',
                Rule::unique('categories', 'woocommerce_category_id')->ignore($categoryId)->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Normalizes is_active, featured, and is_sync_disable to boolean when present.
     */
    protected function prepareForValidation(): void
    {
        $toBoolean = fn ($val) => filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($this->has('is_active')) {
            $this->merge(['is_active' => $toBoolean($this->is_active)]);
        }
        if ($this->has('featured')) {
            $this->merge(['featured' => $toBoolean($this->featured)]);
        }
        if ($this->has('is_sync_disable')) {
            $this->merge(['is_sync_disable' => $toBoolean($this->is_sync_disable)]);
        }
    }
}