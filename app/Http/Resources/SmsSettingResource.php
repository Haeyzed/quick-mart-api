<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ExternalService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource for ExternalService (SMS provider) entity.
 *
 * Transforms SMS provider model into a consistent JSON structure for API responses.
 * Masks sensitive credential keys in details for security.
 *
 * @mixin ExternalService
 */
class SmsSettingResource extends JsonResource
{
    private const MASKED_KEYS = [
        'api_token', 'api_key', 'auth_token', 'password', 'secretkey', 'secret_key',
        'token', 'consumer_key', 'consumer_secret', 'client_secret', 'app_secret',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed SMS provider data for API response.
     */
    public function toArray(Request $request): array
    {
        $details = is_string($this->details)
            ? json_decode($this->details, true) ?? []
            : (array)$this->details;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'details' => $this->maskDetails($details),
            'active' => $this->active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Mask sensitive keys in details array.
     *
     * @param array<string, mixed> $details
     * @return array<string, mixed>
     */
    private function maskDetails(array $details): array
    {
        $masked = [];
        foreach ($details as $key => $value) {
            $normalizedKey = strtolower(str_replace(['-', '_'], '', $key));
            $shouldMask = false;
            foreach (self::MASKED_KEYS as $maskKey) {
                if (str_contains($normalizedKey, str_replace('_', '', $maskKey))) {
                    $shouldMask = true;
                    break;
                }
            }
            $masked[$key] = ($shouldMask && !empty($value)) ? '********' : $value;
        }

        return $masked;
    }
}
