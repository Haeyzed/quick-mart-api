<?php

declare(strict_types=1);

namespace App\Http\Requests\Countries;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateCountryRequest
 *
 * Handles validation and authorization for updating an existing country.
 */
class UpdateCountryRequest extends FormRequest
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
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('status')) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Country|null $country */
        $country = $this->route('country');

        return [
            'iso2' => ['sometimes', 'required', 'string', 'max:2', Rule::unique('countries', 'iso2')->ignore($country)],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['nullable', 'integer', 'in:0,1'],
            'phone_code' => ['nullable', 'string', 'max:255'],
            'iso3' => ['nullable', 'string', 'max:3'],
            'region' => ['nullable', 'string', 'max:255'],
            'subregion' => ['nullable', 'string', 'max:255'],
            'native' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
            'emoji' => ['nullable', 'string', 'max:191'],
            'emojiU' => ['nullable', 'string', 'max:191'],
        ];
    }
}
