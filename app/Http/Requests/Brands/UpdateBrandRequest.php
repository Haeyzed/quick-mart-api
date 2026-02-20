<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateBrandRequest
 *
 * Handles validation and authorization for updating an existing brand.
 */
class UpdateBrandRequest extends FormRequest
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
             * The name of the brand. Must be unique excluding the currently updating brand.
             *
             * @example Apple
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('brands', 'name')->ignore($this->route('brand')),
            ],

            /**
             * A brief description summarizing the brand.
             *
             * @example Technology company
             */
            'short_description' => ['nullable', 'string'],

            /**
             * The SEO title for the brand's public page.
             *
             * @example Apple - Official Site
             */
            'page_title' => ['nullable', 'string', 'max:255'],

            /**
             * The brand's logo or cover image file.
             */
            'image' => ['nullable', 'image', 'max:2048'],

            /**
             * Indicates whether the brand is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],

            /**
             * Optional start date for the brand's active period.
             *
             * @example 2024-01-01
             */
            'start_date' => ['nullable', 'date'],

            /**
             * Optional end date. Must occur on or after the start date.
             *
             * @example 2025-01-01
             */
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * Useful for casting types or manipulating the request payload before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
