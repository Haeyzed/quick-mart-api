<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreUnitRequest
 *
 * Handles validation and authorization for creating a new unit.
 */
class StoreUnitRequest extends FormRequest
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
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
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
             * The unique short code for the unit.
             *
             * @example kg
             */
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->withoutTrashed(),
            ],

            /**
             * The unique display name of the unit.
             *
             * @example Kilogram
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->withoutTrashed(),
            ],

            /**
             * The base unit ID for conversion. Omit or null for a base unit.
             *
             * @example 1
             */
            'base_unit' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->withoutTrashed(),
            ],

            /**
             * The operator for conversion (required when base_unit is set). One of: *, /, +, -
             *
             * @example *
             */
            'operator' => ['nullable', 'string', 'required_with:base_unit', 'in:*,/,+,-'],

            /**
             * The numeric value for conversion (required when base_unit is set). Must be >= 0.
             *
             * @example 1000
             */
            'operation_value' => ['nullable', 'numeric', 'required_with:base_unit', 'min:0'],

            /**
             * Indicates whether the unit should be active upon creation.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
