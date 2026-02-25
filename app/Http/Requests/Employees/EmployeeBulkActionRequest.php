<?php

declare(strict_types=1);

namespace App\Http\Requests\Employees;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class EmployeeBulkActionRequest
 *
 * Handles validation and authorization for performing bulk actions on employee records.
 */
class EmployeeBulkActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /**
             * An array of valid employee IDs.
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single employee ID ensuring it exists in the database.
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('employees', 'id')->withoutTrashed()],
        ];
    }
}
