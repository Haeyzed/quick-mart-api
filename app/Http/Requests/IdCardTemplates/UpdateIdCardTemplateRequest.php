<?php

declare(strict_types=1);

namespace App\Http\Requests\IdCardTemplates;

use App\Http\Requests\BaseRequest;
use App\Models\IdCardTemplate;
use Illuminate\Validation\Rule;

/**
 * Class UpdateIdCardTemplateRequest
 *
 * Handles validation and authorization for updating an existing ID card template.
 */
class UpdateIdCardTemplateRequest extends BaseRequest
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
        /** @var IdCardTemplate|null $template */
        $template = $this->route('id_card_template');

        return [
            /**
             * The name of the ID card template.
             * @example Standard Corporate ID
             */
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('id_card_templates', 'name')->ignore($template)
            ],

            /**
             * The JSON configuration for the ID card design.
             * @example {"primary_color": "#171f27", "text_color": "#ffffff", "show_qr_code": true}
             */
            'design_config' => ['sometimes', 'required', 'array'],

            /**
             * Determines if the template is active.
             * @example true
             */
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
