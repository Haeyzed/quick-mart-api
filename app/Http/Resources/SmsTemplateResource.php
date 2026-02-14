<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for SmsTemplate entity.
 *
 * Transforms SmsTemplate model into a consistent JSON structure for API responses.
 *
 * @mixin SmsTemplate
 */
class SmsTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed SMS template data for API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'content' => $this->content,
            'is_default' => $this->is_default,
            'is_default_ecommerce' => $this->is_default_ecommerce,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
