<?php

declare(strict_types=1);

namespace App\Http\Requests\Cities;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCityRequest
 *
 * Handles validation and authorization for updating an existing city.
 */
class UpdateCityRequest extends FormRequest
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
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'country_id' => ['sometimes', 'required', 'integer', Rule::exists('countries', 'id')],
            'state_id' => ['sometimes', 'required', 'integer', Rule::exists('states', 'id')],
            'country_code' => ['nullable', 'string', 'max:2'],
            'state_code' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
        ];
    }
}
