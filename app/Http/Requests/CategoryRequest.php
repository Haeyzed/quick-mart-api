<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * CategoryRequest
 *
 * Validates incoming data for both creating and updating categories.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class CategoryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $categoryId = $this->route('category');

        return [
            /**
             * The category name. Must be unique across all categories.
             *
             * @var string @name
             * @example Electronics
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($categoryId),
            ],
            /**
             * URL-friendly slug for the category. Auto-generated from name if not provided.
             *
             * @var string|null @slug
             * @example electronics
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('categories', 'slug')->ignore($categoryId),
            ],
            /**
             * Brief description of the category.
             *
             * @var string|null @short_description
             * @example High-end electronics and gadgets
             */
            'short_description' => ['nullable', 'string', 'max:1000'],
            /**
             * SEO page title for the category.
             *
             * @var string|null @page_title
             * @example Shop Electronics | Best Deals
             */
            'page_title' => ['nullable', 'string', 'max:255'],
            /**
             * Category image file. Accepts JPEG, PNG, JPG, GIF, or WebP format. Max 5MB.
             * The full URL will be saved to the image field after upload.
             *
             * @var UploadedFile|null @image
             * @example image.jpg
             */
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            /**
             * Category icon class name or identifier.
             *
             * @var string|null @icon
             * @example fa fa-electronics
             */
            'icon' => ['nullable', 'string', 'max:255'],
            /**
             * Parent category ID for hierarchical structure. Must reference existing category.
             *
             * @var int|null @parent_id
             * @example 1
             */
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($categoryId) {
                    if ($categoryId) {
                        $query->where('id', '!=', $categoryId);
                    }
                }),
            ],
            /**
             * Whether the category is active and visible.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
            /**
             * Whether the category is featured on the homepage.
             *
             * @var bool|null @featured
             * @example false
             */
            'featured' => ['nullable', 'boolean'],
            /**
             * Whether sync to external systems is disabled.
             *
             * @var bool|null @is_sync_disable
             * @example false
             */
            'is_sync_disable' => ['nullable', 'boolean'],
            /**
             * WooCommerce category ID for sync purposes. Must be unique.
             *
             * @var int|null @woocommerce_category_id
             * @example 123
             */
            'woocommerce_category_id' => [
                'nullable',
                'integer',
                Rule::unique('categories', 'woocommerce_category_id')->ignore($categoryId),
            ],
        ];
    }

    /**
     * Get custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.unique' => 'A category with this name already exists.',
            'slug.unique' => 'A category with this slug already exists.',
            'parent_id.exists' => 'The selected parent category does not exist.',
            'parent_id.integer' => 'The parent category ID must be a valid integer.',
            'woocommerce_category_id.unique' => 'This WooCommerce ID is already assigned to another category.',
            'image.file' => 'The image must be a valid file.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'The image size cannot exceed 5MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method runs BEFORE validation and is the correct place to:
     * - Normalize string inputs (trim, whitespace cleanup)
     * - Set default null values for optional fields
     * - Transform data that needs to be validated in a specific format
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize name (replace multiple whitespaces with single space)
        $name = $this->name ? preg_replace('/\s+/', ' ', trim($this->name)) : null;

        $this->merge([
            'name' => $name,
            'slug' => $this->slug ?: null,
            'short_description' => $this->short_description ?: null,
            'page_title' => $this->page_title ?: null,
            'icon' => $this->icon ?: null,
            'parent_id' => $this->parent_id ?: null,
            'woocommerce_category_id' => $this->woocommerce_category_id ?: null,
        ]);
    }
}

