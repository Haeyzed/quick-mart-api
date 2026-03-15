<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Arr;

/**
 * Category index/list filter request.
 *
 * Validates and normalizes filter parameters for the categories listing endpoint.
 * Follows the same array/prepare pattern as BrandFilterRequest (e.g. is_active, featured, is_sync_disable as whereIn arrays).
 */
class CategoryFilterRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     * Intercepts comma-separated strings and converts them to arrays for filter fields.
     */
    protected function prepareForValidation(): void
    {
        $arrayFields = ['is_active', 'featured', 'is_sync_disable'];

        foreach ($arrayFields as $field) {
            if ($this->has($field)) {
                $value = $this->input($field);

                if (is_string($value)) {
                    $this->merge([
                        $field => array_values(array_filter(
                            explode(',', $value),
                            fn ($val) => trim((string) $val) !== ''
                        )),
                    ]);
                } elseif (! is_array($value)) {
                    $this->merge([
                        $field => Arr::wrap($value),
                    ]);
                }
            }
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
             * Search term to filter categories by name or slug.
             *
             * @example "Electronics"
             */
            'search' => ['nullable', 'string'],

            /**
             * Filter by active status. Accepts comma-separated or array of booleans (0/1).
             *
             * @example "1,0" or ["1", "0"]
             */
            'is_active' => ['nullable', 'array'],
            'is_active.*' => ['boolean'],

            /**
             * Filter by featured status. Accepts comma-separated or array of booleans (0/1).
             *
             * @example "1,0" or ["1", "0"]
             */
            'featured' => ['nullable', 'array'],
            'featured.*' => ['boolean'],

            /**
             * Filter by sync disabled status. Accepts comma-separated or array of booleans (0/1).
             *
             * @example "1,0" or ["1", "0"]
             */
            'is_sync_disable' => ['nullable', 'array'],
            'is_sync_disable.*' => ['boolean'],

            /**
             * Filter by parent category ID.
             *
             * @example 1
             */
            'parent_id' => ['nullable', 'integer', 'exists:categories,id'],

            /**
             * Filter categories starting from this date.
             *
             * @example "2024-01-01"
             */
            'start_date' => ['nullable', 'date'],

            /**
             * Filter categories up to this date.
             *
             * @example "2024-12-31"
             */
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],

            /**
             * Amount of items to return per page for pagination.
             *
             * @example 50
             */
            'per_page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
