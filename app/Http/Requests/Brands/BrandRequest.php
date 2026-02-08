<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class BrandRequest
 *
 * Validates brand creation and update requests.
 */
class BrandRequest extends BaseRequest
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
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        // Safely get the brand ID from the route for ignore rule
        $brandId = $this->route('brand')?->id;

        return [
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('brands', 'name')->ignore($brandId)
            ],
            'slug' => [
                'nullable', 
                'string', 
                'max:255', 
                Rule::unique('brands', 'slug')->ignore($brandId)
            ],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'page_title' => ['nullable', 'string', 'max:255'],
            'image' => [
                'nullable', 
                'image', 
                'mimes:jpeg,png,jpg,webp', 
                'max:5120' // 5MB
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Prepare data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}