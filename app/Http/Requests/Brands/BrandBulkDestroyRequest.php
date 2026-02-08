<?php

declare(strict_types=1);

namespace App\Http\Requests\Brands;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class BrandBulkDestroyRequest
 */
class BrandBulkDestroyRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', Rule::exists('brands', 'id')],
        ];
    }
}