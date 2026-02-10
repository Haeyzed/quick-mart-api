<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Http\Requests\BaseRequest;

/**
 * Form request for adding or updating customer reward points.
 */
class CustomerPointRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'points' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:500'],
        ];
    }
}
