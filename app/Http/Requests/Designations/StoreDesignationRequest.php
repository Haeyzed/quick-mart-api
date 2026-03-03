<?php

declare(strict_types=1);

namespace App\Http\Requests\Designations;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreDesignationRequest
 *
 * Handles validation and authorization for creating a new designation.
 */
class StoreDesignationRequest extends BaseRequest
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
        return [
            /**
             * The unique name of the designation.
             *
             * @example Software Engineer
             */
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('designations', 'name')->withoutTrashed(),
            ],

            
            'department_id' => ['required', 'integer', 'exists:departments,id'],

            /**
             * Indicates whether the designation is active.
             *
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
