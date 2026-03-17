<?php

declare(strict_types=1);

namespace App\Http\Requests\Warehouses;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Arr;

/**
 * Warehouse index/list filter request.
 *
 * Validates and normalizes filter parameters for the warehouses listing endpoint.
 * Follows the same array/prepare pattern as BrandFilterRequest (e.g. is_active as whereIn array).
 */
class WarehouseFilterRequest extends BaseRequest
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
        $arrayFields = ['is_active'];

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
             * Search term to filter warehouses by name, email, or phone number.
             *
             * @example "Main"
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
             * Filter warehouses starting from this date.
             *
             * @example "2024-01-01"
             */
            'start_date' => ['nullable', 'date'],

            /**
             * Filter warehouses up to this date.
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
