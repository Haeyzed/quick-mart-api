<?php

declare(strict_types=1);

namespace App\Http\Requests\Designations;

use App\Models\Designation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateDesignationRequest
 *
 * Handles validation and authorization for updating an existing designation.
 */
class UpdateDesignationRequest extends FormRequest
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
        /** @var Designation|null $designation */
        $designation = $this->route('designation');

        return [
            /**
             * The unique name of the designation.
             *
             * @example Senior Software Engineer
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('designations', 'name')->ignore($designation)->withoutTrashed(),
            ],

            /**
             * Indicates whether the designation is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
