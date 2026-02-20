<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StoreBrandRequest
 *
 * Handles validation and authorization for creating a new brand.
 */
class StoreBrandRequest extends FormRequest
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
             * The unique name of the brand.
             *
             * @example Samsung
             */
            'name' => ['required', 'string', 'max:255', 'unique:brands,name'],

            /**
             * A brief description summarizing the brand.
             *
             * @example Leading electronics manufacturer
             */
            'short_description' => ['nullable', 'string'],

            /**
             * The SEO title for the brand's public page.
             *
             * @example Samsung Electronics Official Store
             */
            'page_title' => ['nullable', 'string', 'max:255'],

            /**
             * The brand's logo or cover image file.
             */
            'image' => ['nullable', 'image', 'max:2048'],

            /**
             * Indicates whether the brand should be active upon creation.
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
             * @example 2024-12-31
             */
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * This method is called before the validation rules are evaluated.
     * You can use it to sanitize or format inputs (e.g., casting string booleans to actual booleans).
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
