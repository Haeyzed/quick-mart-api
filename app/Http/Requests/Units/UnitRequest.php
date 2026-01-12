<?php

declare(strict_types=1);

namespace App\Http\Requests\Units;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * UnitRequest
 *
 * Validates incoming data for both creating and updating units.
 * Handles both store and update operations with appropriate uniqueness constraints.
 */
class UnitRequest extends BaseRequest
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
        $unitId = $this->route('unit');

        return [
            /**
             * Unique code identifier for the unit.
             *
             * @var string $code
             * @example KG
             */
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'code')->ignore($unitId),
            ],
            /**
             * Display name of the unit.
             *
             * @var string $name
             * @example Kilogram
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'name')->ignore($unitId),
            ],
            /**
             * Base unit ID for conversion. If null, this is a base unit.
             *
             * @var int|null $base_unit
             * @example 1
             */
            'base_unit' => [
                'nullable',
                'integer',
                Rule::exists('units', 'id')->where(function ($query) use ($unitId) {
                    if ($unitId) {
                        $query->where('id', '!=', $unitId);
                    }
                }),
            ],
            /**
             * Mathematical operator for conversion (*, /, +, -).
             *
             * @var string|null $operator
             * @example *
             */
            'operator' => ['nullable', 'string', 'in:*,/,+,-'],
            /**
             * Value to use with operator for conversion.
             *
             * @var float|null $operation_value
             * @example 1000
             */
            'operation_value' => ['nullable', 'numeric', 'min:0'],
            /**
             * Whether the unit is active and visible.
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
            'code.required' => 'Unit code is required.',
            'code.unique' => 'A unit with this code already exists.',
            'name.required' => 'Unit name is required.',
            'name.unique' => 'A unit with this name already exists.',
            'base_unit.exists' => 'The selected base unit does not exist.',
            'operator.in' => 'The operator must be one of: *, /, +, -.',
            'operation_value.numeric' => 'The operation value must be a number.',
            'operation_value.min' => 'The operation value must be greater than or equal to 0.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert is_active to boolean if present
        // Handles strings like "true", "false", "1", "0", etc.
        $isActive = $this->has('is_active') && $this->is_active !== null
            ? filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : null;

        $this->merge([
            'base_unit' => $this->base_unit ?: null,
            'operator' => $this->operator ?: null,
            'operation_value' => $this->operation_value ?: null,
            'is_active' => $isActive,
        ]);
    }

}

