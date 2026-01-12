<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * BrandRequest
 *
 * Validates incoming data for both creating and updating brands.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class BrandRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $brandId = $this->route('brand');

        return [
            /**
             * The brand name. Must be unique across all brands.
             *
             * @var string $name
             * @example Apple
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($brandId),
            ],
            /**
             * URL-friendly slug for the brand. Auto-generated from name if not provided.
             *
             * @var string|null $slug
             * @example apple
             */
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug')->ignore($brandId),
            ],
            /**
             * Brief description of the brand.
             *
             * @var string|null $short_description
             * @example Premium technology brand
             */
            'short_description' => ['nullable', 'string', 'max:1000'],
            /**
             * SEO page title for the brand.
             *
             * @var string|null $page_title
             * @example Shop Apple Products | Best Deals
             */
            'page_title' => ['nullable', 'string', 'max:255'],
            /**
             * Brand image file. Accepts JPEG, PNG, JPG, GIF, or WebP format. Max 5MB.
             * The full URL will be saved to the image_url field after upload.
             *
             * @var UploadedFile|null $image
             * @example image.jpg
             */
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            /**
             * Whether the brand is active and visible.
             *
             * @var bool|null $is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
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
            'name.required' => 'Brand name is required.',
            'name.unique' => 'A brand with this name already exists.',
            'slug.unique' => 'A brand with this slug already exists.',
            'image.file' => 'The image must be a valid file.',
            'image.image' => 'The image must be a valid image file.',
            'image.mimes' => 'The image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'image.max' => 'The image size cannot exceed 5MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize name (replace multiple whitespaces with single space)
        $name = $this->name ? preg_replace('/\s+/', ' ', trim($this->name)) : null;

        // Convert is_active to boolean if present
        // Handles strings like "true", "false", "1", "0", etc.
        $isActive = $this->has('is_active') && $this->is_active !== null
            ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $this->merge([
            'name' => $name,
            'slug' => $this->slug ?: null,
            'short_description' => $this->short_description ?: null,
            'page_title' => $this->page_title ?: null,
            'is_active' => $isActive,
        ]);
    }

}

