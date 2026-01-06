<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * ExpenseCategoryRequest
 *
 * Validates incoming data for both creating and updating expense categories.
 */
class ExpenseCategoryRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
        $expenseCategoryId = $this->route('expense_category');

        return [
            /**
             * Unique code identifier for the expense category.
             *
             * @var string @code
             * @example EXP001
             */
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'code')->ignore($expenseCategoryId),
            ],
            /**
             * The expense category name.
             *
             * @var string @name
             * @example Office Supplies
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Whether the expense category is active.
             *
             * @var bool|null @is_active
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => $this->code ? trim($this->code) : null,
            'name' => $this->name ? trim($this->name) : null,
        ]);
    }

}

