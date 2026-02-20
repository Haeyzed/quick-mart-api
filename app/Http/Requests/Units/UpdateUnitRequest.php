<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateUnitRequest
 *
 * Handles validation and authorization for updating an existing unit.
 */
class UpdateUnitRequest extends FormRequest
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
        /** @var Unit|null $unit */
        $unit = $this->route('unit');

        return [
            /**
             * The short code of the unit. Must be unique excluding the currently updating unit.
             *
             * @example kg
             */
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->ignore($unit)->withoutTrashed(),
            ],

            /**
             * The name of the unit. Must be unique excluding the currently updating unit.
             *
             * @example Kilogram
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unit)->withoutTrashed(),
            ],

            /**
             * The base unit ID. Null for a base unit. Cannot be the unit itself.
             *
             * @example 1
             */
            'base_unit' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->whereNot('id', $unit?->id)->withoutTrashed(),
            ],

            /**
             * The operator for conversion. One of: *, /, +, -
             *
             * @example *
             */
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],

            /**
             * The numeric value for conversion. Must be >= 0.
             *
             * @example 1000
             */
            'operation_value' => ['nullable', 'numeric', 'min:0'],

            /**
             * Indicates whether the unit is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
