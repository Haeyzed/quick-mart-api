<?php

declare(strict_types=1);

namespace App\Http\Requests\Categories;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CategoryBulkUpdateRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', Rule::exists('categories', 'id')],
            'action' => [
                'required', 
                'string', 
                Rule::in(['activate', 'deactivate', 'enable_featured', 'disable_featured', 'enable_sync', 'disable_sync'])
            ],
        ];
    }
}