<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCategoryRequest
 *
 * Handles validation and authorization for updating an existing category.
 */
class UpdateCategoryRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * Useful for casting types or manipulating the request payload before validation.
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Category|null $category */
        $category = $this->route('category');

        return [
            /**
             * The name of the category. Must be unique excluding the currently updating category.
             *
             * @example Electronics
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category)->withoutTrashed(),
            ],

            /**
             * Optional URL-friendly slug. Must be unique excluding the current category.
             *
             * @example electronics
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($category)->withoutTrashed(),
            ],

            /**
             * A brief description summarizing the category.
             *
             * @example Consumer electronics
             */
            'short_description' => ['nullable', 'string', 'max:1000'],

            /**
             * The SEO title for the category's public page.
             *
             * @example Electronics - Shop
             */
            'page_title' => ['nullable', 'string', 'max:255'],

            /**
             * The category's cover or banner image file.
             */
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120',
            ],

            /**
             * The category's icon file.
             */
            'icon' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp,svg',
                'max:5120',
            ],

            /**
             * The parent category ID. Null for root. Cannot be self.
             *
             * @example 2
             */
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')
                    ->whereNot('id', $category->id ?? null)
                    ->withoutTrashed(),
            ],

            /**
             * Indicates whether the category is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Indicates whether the category is featured.
             *
             * @example true
             */
            'featured' => ['nullable', 'boolean'],

            /**
             * Indicates whether sync is disabled for this category.
             *
             * @example false
             */
            'is_sync_disable' => ['nullable', 'boolean'],

            /**
             * Optional WooCommerce category ID. Must be unique excluding the current category.
             *
             * @example 42
             */
            'woocommerce_category_id' => [
                'nullable',
                'integer',
                Rule::unique('categories', 'woocommerce_category_id')->ignore($category)->withoutTrashed(),
            ],
        ];
    }
}
