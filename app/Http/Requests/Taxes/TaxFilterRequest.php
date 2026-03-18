<?php

declare(strict_types=1);

namespace App\Http\Requests\Taxes;

use App\Http\Requests\BaseRequest;
use Illuminate\Support\Arr;

/**
 * Tax index/list filter request.
 *
 * Validates and normalizes filter parameters for the taxes listing endpoint.
 * Mirrors the array/prepare pattern used by UnitFilterRequest (e.g. is_active as whereIn array),
 * while keeping backwards compatibility with the legacy boolean "status" flag.
 */
class TaxFilterRequest extends BaseRequest
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
     *
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
                            static fn ($val) => trim((string) $val) !== ''
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
             * Search term to filter taxes by name.
             *
             * @example "VAT"
             */
            'search' => ['nullable', 'string'],

            /**
             * Filter by active status using an array of booleans (0/1), like units.
             *
             * @example "1,0" or ["1", "0"]
             */
            'is_active' => ['nullable', 'array'],
            'is_active.*' => ['boolean'],

            /**
             * Legacy single-status boolean (kept for backwards compatibility with older frontends).
             *
             * @example true
             */
            'status' => ['nullable', 'boolean'],

            /**
             * Filter taxes starting from this date.
             *
             * @example "2024-01-01"
             */
            'start_date' => ['nullable', 'date'],

            /**
             * Filter taxes up to this date.
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
