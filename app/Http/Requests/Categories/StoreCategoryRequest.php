<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreCategoryRequest
 *
 * Handles validation and authorization for creating a new category.
 */
class StoreCategoryRequest extends FormRequest
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
     * You can use it to sanitize or format inputs (e.g., casting string booleans to actual booleans).
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
        return [
            /**
             * The unique name of the category.
             *
             * @example Electronics
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->withoutTrashed(),
            ],

            /**
             * Optional URL-friendly slug. If omitted, generated from name.
             *
             * @example electronics
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->withoutTrashed(),
            ],

            /**
             * A brief description summarizing the category.
             *
             * @example Consumer electronics and gadgets
             */
            'short_description' => ['nullable', 'string', 'max:1000'],

            /**
             * The SEO title for the category's public page.
             *
             * @example Electronics - Shop Now
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
             * The category's icon file (e.g. for menus).
             */
            'icon' => [
                'nullable',
                'file',
                'mimes:jpeg,png,jpg,webp,svg',
                'max:5120',
            ],

            /**
             * The parent category ID. Omit or null for root category.
             *
             * @example 1
             */
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->withoutTrashed(),
            ],

            /**
             * Indicates whether the category should be active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Indicates whether the category is featured.
             *
             * @example false
             */
            'featured' => ['nullable', 'boolean'],

            /**
             * Indicates whether sync (e.g. WooCommerce) is disabled for this category.
             *
             * @example false
             */
            'is_sync_disable' => ['nullable', 'boolean'],

            /**
             * Optional WooCommerce category ID for sync.
             *
             * @example 42
             */
            'woocommerce_category_id' => [
                'nullable',
                'integer',
                Rule::unique('categories', 'woocommerce_category_id')->withoutTrashed(),
            ],
        ];
    }
}
