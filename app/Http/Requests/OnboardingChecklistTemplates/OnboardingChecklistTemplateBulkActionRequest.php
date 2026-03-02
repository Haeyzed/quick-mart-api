<?php

declare(strict_types=1);

namespace App\Http\Requests\OnboardingChecklistTemplates;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class OnboardingChecklistTemplateBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on onboarding checklist templates.
 */
class OnboardingChecklistTemplateBulkActionRequest extends BaseRequest
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
             * An array of valid onboarding checklist template IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single template ID ensuring it exists in the database.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('onboarding_checklist_templates', 'id')],
        ];
    }
}
