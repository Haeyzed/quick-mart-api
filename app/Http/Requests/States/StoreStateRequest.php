<?php

declare(strict_types=1);

namespace App\Http\Requests\States;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class StoreStateRequest
 *
 * Handles validation and authorization for creating a new state.
 */
class StoreStateRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'code' => ['nullable', 'string', 'max:255'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'state_code' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
        ];
    }
}
