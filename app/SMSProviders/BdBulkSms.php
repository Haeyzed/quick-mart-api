<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\SendSmsInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * BD Bulk SMS Provider
 *
 * Implementation of SMS sending for BD Bulk SMS service (GreenWeb API).
 *
 * @package App\SMSProviders
 */
class BdBulkSms implements SendSmsInterface
{
    private const API_BASE_URL = 'http://api.greenweb.com.bd/api.php?json';

    /**
     * Send an SMS message via BD Bulk SMS service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'recipent': string - Recipient phone number
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing token
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $token = $details['token'] ?? '';

            if (empty($token)) {
                Log::error('BdBulkSms: Missing API token');
                return false;
            }

            $params = [
                'token' => $token,
                'to' => $data['recipent'] ?? '',
                'message' => $data['message'] ?? '',
            ];

            $response = Http::asForm()->post(self::API_BASE_URL, $params);

            if ($response->successful()) {
                return $response->json() ?? true;
            }

            Log::error('BdBulkSms: Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('BdBulkSms: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

