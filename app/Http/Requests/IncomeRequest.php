<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

/**
 * IncomeRequest
 *
 * Validates incoming data for both creating and updating incomes.
 */
class IncomeRequest extends BaseRequest
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
        return [
            /**
             * Reference number for the income. If not provided, will be auto-generated.
             *
             * @var string|null @reference_no
             * @example ir-20240101-120000
             */
            'reference_no' => ['nullable', 'string', 'max:255'],
            /**
             * The income category ID.
             *
             * @var int @income_category_id
             * @example 1
             */
            'income_category_id' => ['required', 'integer', Rule::exists('income_categories', 'id')],
            /**
             * The warehouse ID.
             *
             * @var int @warehouse_id
             * @example 1
             */
            'warehouse_id' => ['required', 'integer', Rule::exists('warehouses', 'id')],
            /**
             * The account ID.
             *
             * @var int @account_id
             * @example 1
             */
            'account_id' => ['required', 'integer', Rule::exists('accounts', 'id')],
            /**
             * The user ID. If not provided, uses authenticated user.
             *
             * @var int|null @user_id
             * @example 1
             */
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            /**
             * The cash register ID. Auto-assigned if user has open cash register.
             *
             * @var int|null @cash_register_id
             * @example 1
             */
            'cash_register_id' => ['nullable', 'integer', Rule::exists('cash_registers', 'id')],
            /**
             * The income amount.
             *
             * @var float @amount
             * @example 1000.00
             */
            'amount' => ['required', 'numeric', 'min:0'],
            /**
             * Optional note about the income.
             *
             * @var string|null @note
             * @example Payment received for services
             */
            'note' => ['nullable', 'string'],
            /**
             * The date and time of the income. If not provided, uses current date/time.
             *
             * @var string|null @created_at
             * @example 2024-01-01 12:00:00
             */
            'created_at' => ['nullable', 'date'],
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Normalize date format (convert "/" to "-" and format to Y-m-d H:i:s)
        $createdAt = $this->created_at;
        if ($createdAt) {
            $createdAt = str_replace('/', '-', $createdAt);
            // If only date provided, add time
            if (strlen($createdAt) <= 10) {
                $createdAt = date('Y-m-d H:i:s', strtotime($createdAt));
            } else {
                $createdAt = date('Y-m-d H:i:s', strtotime($createdAt));
            }
        }

        $this->merge([
            'reference_no' => $this->reference_no ? trim($this->reference_no) : null,
            'note' => $this->note ? trim($this->note) : null,
            'created_at' => $createdAt,
        ]);
    }

}

