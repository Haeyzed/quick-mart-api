<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentGatewayResource extends JsonResource
{
    private const MASKED_KEYS = [
        'api_key', 'api_token', 'auth_token', 'password', 'secret', 'secret_key',
        'token', 'consumer_key', 'consumer_secret', 'client_secret', 'app_secret',
        'private_key', 'callback_token',
    ];

    /**
     * Transform the resource into an array.
     *
     * @param Request $request The incoming HTTP request.
     * @return array<string, mixed> The transformed payment gateway data for API response.
     */
    public function toArray(Request $request): array
    {
        $details = $this->parseDetails($this->details);
        $moduleStatus = is_string($this->module_status)
            ? json_decode($this->module_status, true) ?? []
            : (array) $this->module_status;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'details' => $this->maskDetails($details),
            'module_status' => $moduleStatus,
            'active' => $this->active,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * @param array<string, string> $details
     * @return array<string, string>
     */
    private function maskDetails(array $details): array
    {
        $masked = [];
        foreach ($details as $key => $value) {
            $normalized = strtolower(str_replace(['-', ' ', '_'], '', $key));
            $shouldMask = false;
            foreach (self::MASKED_KEYS as $mk) {
                if (str_contains($normalized, str_replace('_', '', $mk))) {
                    $shouldMask = true;
                    break;
                }
            }
            $masked[$key] = ($shouldMask && $value !== '') ? '********' : $value;
        }

        return $masked;
    }

    private function parseDetails(?string $details): array
    {
        if (empty($details) || ! str_contains($details, ';')) {
            return [];
        }
        [$keysStr, $valsStr] = explode(';', $details, 2);
        $keys = array_map('trim', explode(',', $keysStr));
        $vals = array_map('trim', explode(',', $valsStr));

        return array_combine($keys, $vals) ?: [];
    }
}
