<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * IncomeCategoryRequest
 *
 * Validates incoming data for both creating and updating income categories.
 */
class IncomeCategoryRequest extends BaseRequest
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
        $incomeCategoryId = $this->route('income_category');

        return [
            /**
             * Unique code identifier for the income category. If not provided, will be auto-generated.
             *
             * @var string|null @code
             * @example 12345678
             */
            'code' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('income_categories', 'code')
                    ->where(function ($query) {
                        return $query->where('is_active', true);
                    })
                    ->ignore($incomeCategoryId),
            ],
            /**
             * The income category name.
             *
             * @var string @name
             * @example Sales Revenue
             */
            'name' => ['required', 'string', 'max:255'],
            /**
             * Whether the income category is active.
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

