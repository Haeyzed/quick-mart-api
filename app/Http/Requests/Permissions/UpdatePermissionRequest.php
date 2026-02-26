<?php

declare(strict_types=1);

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseRequest;
use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdatePermissionRequest
 *
 * Handles validation and authorization for updating an existing permission.
 */
class UpdatePermissionRequest extends BaseRequest
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
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        /** @var Permission|null $permission */
        $permission = $this->route('permission');
        $guardName = $this->input('guard_name', $permission?->guard_name ?? 'web');

        return [
            /**
             * The name of the permission.
             * @example view employees
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')->where('guard_name', $guardName)->ignore($permission),
            ],

            /**
             * The authentication guard the permission belongs to.
             * @example web
             */
            'guard_name' => ['nullable', 'string', 'max:255'],

            /**
             * The description of the permission's purpose.
             * @example Manages all human resource activities.
             */
            'description' => ['nullable', 'string', 'max:500'],

            /**
             * The system module this permission relates to.
             * @example hrm
             */
            'module' => ['nullable', 'string', 'max:255'],

            /**
             * Determines if the permission is active.
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
