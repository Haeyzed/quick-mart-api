<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Http\Requests\BaseRequest;
class RewardPointSettingRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_point_amount' => ['nullable', 'numeric', 'min:0'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'duration' => ['nullable', 'integer', 'min:0'],
            'type' => ['nullable', 'string', 'in:days,months,years'],
            'is_active' => ['nullable', 'boolean'],
            'redeem_amount_per_unit_rp' => ['nullable', 'numeric', 'min:0'],
            'min_order_total_for_redeem' => ['nullable', 'numeric', 'min:0'],
            'min_redeem_point' => ['nullable', 'integer', 'min:0'],
            'max_redeem_point' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
