<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for MailSetting entity.
 *
 * Transforms MailSetting model into a consistent JSON structure for API responses.
 * Masks password for security.
 *
 * @mixin \App\Models\MailSetting
 */
class MailSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed mail setting data for API response.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'driver' => $this->driver,
            'host' => $this->host,
            'port' => $this->port,
            'from_address' => $this->from_address,
            'from_name' => $this->from_name,
            'username' => $this->username,
            'password' => $this->password ? '********' : null,
            'encryption' => $this->encryption,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
