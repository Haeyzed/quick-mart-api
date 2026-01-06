<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\SendSmsInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reve SMS Provider
 *
 * Implementation of SMS sending for Reve SMS service.
 *
 * @package App\SMSProviders
 */
class ReveSms implements SendSmsInterface
{
    private const API_BASE_URL = 'http://smpp.revesms.com:7788/sendtext';

    /**
     * Send an SMS message via Reve SMS service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'recipent': string - Recipient phone number
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing apikey, secretkey, and callerID
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $apiKey = $details['apikey'] ?? '';
            $secretKey = $details['secretkey'] ?? '';
            $callerId = $details['callerID'] ?? '';

            if (empty($apiKey) || empty($secretKey) || empty($callerId)) {
                Log::error('ReveSms: Missing required credentials');
                return false;
            }

            $params = [
                'apikey' => $apiKey,
                'secretkey' => $secretKey,
                'callerID' => $callerId,
                'toUser' => $data['recipent'] ?? '',
                'messageContent' => $data['message'] ?? '',
            ];

            $response = Http::get(self::API_BASE_URL, $params);

            if ($response->successful()) {
                return $response->json() ?? true;
            }

            Log::error('ReveSms: Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('ReveSms: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

