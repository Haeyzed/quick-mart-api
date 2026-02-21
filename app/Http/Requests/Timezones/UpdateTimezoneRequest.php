<?php

declare(strict_types=1);

namespace App\Http\Requests\Timezones;

use App\Models\Timezone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Class UpdateTimezoneRequest
 *
 * Handles validation and authorization for updating an existing timezone.
 */
class UpdateTimezoneRequest extends FormRequest
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
        ];
    }
}
