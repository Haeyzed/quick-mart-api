<?php

declare(strict_types=1);

namespace App\Http\Requests\Payrolls;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class PayrollBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on payroll records.
 */
class PayrollBulkActionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
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
             * An array of valid payroll IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single payroll ID ensuring it exists in the database.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('payrolls', 'id')->withoutTrashed()],
        ];
    }
}
