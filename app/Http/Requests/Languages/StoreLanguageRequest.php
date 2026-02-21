<?php

declare(strict_types=1);

namespace App\Http\Requests\Languages;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreLanguageRequest
 *
 * Handles validation and authorization for creating a new language.
 */
class StoreLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool True if authorized, false otherwise.
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
            'code' => ['required', 'string', 'max:2', Rule::unique('languages', 'code')],
            'name' => ['required', 'string', 'max:255'],
            'name_native' => ['nullable', 'string', 'max:255'],
            'dir' => ['nullable', 'string', 'in:ltr,rtl'],
        ];
    }
}
