<?php

declare(strict_types=1);

namespace App\Http\Requests\CustomerGroups;

use App\Http\Requests\BaseRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * Form request for customer group create and update validation.
 *
 * Validates name, percentage, and is_active.
 */
class CustomerGroupRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True to allow (authorization handled by middleware/policy).
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, string|ValidationRule>>
     */
    public function rules(): array
    {
        $customerGroupId = $this->route('customer_group')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('customer_groups', 'name')->ignore($customerGroupId),
            ],
            'percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * Normalizes strings and is_active to boolean when present.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => $this->name ? trim($this->name) : null,
        ]);
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
