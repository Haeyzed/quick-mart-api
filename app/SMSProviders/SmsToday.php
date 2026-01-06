<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\SendSmsInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMS Today Provider
 *
 * Implementation of SMS sending for SMS Today service.
 *
 * @package App\SMSProviders
 */
class SmsToday implements SendSmsInterface
{
    private const API_BASE_URL = 'https://api.smstoday.net/send';

    /**
     * Send an SMS message via SMS Today service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'numbers': string|array - Recipient phone number(s)
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing api_key, password, and from
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $apiKey = $details['api_key'] ?? '';
            $password = $details['password'] ?? '';
            $from = $details['from'] ?? '';

            if (empty($apiKey) || empty($password) || empty($from)) {
                Log::error('SmsToday: Missing required credentials');
                return false;
            }

            // Handle both single number and array of numbers
            $numbers = $data['numbers'] ?? '';
            if (is_array($numbers)) {
                $numbers = implode(',', $numbers);
            }

            $params = [
                'text' => $data['message'] ?? '',
                'numbers' => $numbers,
                'api_key' => $apiKey,
                'password' => $password,
                'from' => $from,
            ];

            $response = Http::asForm()->post(self::API_BASE_URL, $params);

            if ($response->successful()) {
                return $response->json() ?? true;
            }

            Log::error('SmsToday: Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('SmsToday: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

