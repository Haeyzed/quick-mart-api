<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class BrandRequest
 * @property-read string $name
 * @property-read bool|null $is_active
 */
class BrandRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        $brandId = $this->route('brand')?->id;

        return [
            'name'              => ['required', 'string', 'max:255', Rule::unique('brands', 'name')->ignore($brandId)],
            'slug'              => ['nullable', 'string', 'max:255', Rule::unique('brands', 'slug')->ignore($brandId)],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'page_title'        => ['nullable', 'string', 'max:255'],
            'image'             => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'is_active'         => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}