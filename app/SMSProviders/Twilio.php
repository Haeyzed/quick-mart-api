<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\SendSmsInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Twilio SMS Provider
 *
 * Implementation of SMS sending for Twilio SMS service.
 * Note: The original implementation appears to use SMS Today endpoint,
 * but this refactored version uses proper Twilio API.
 *
 * @package App\SMSProviders
 */
class Twilio implements SendSmsInterface
{
    /**
     * Send an SMS message via Twilio SMS service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'recipent': string - Recipient phone number (E.164 format)
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing account_sid, auth_token, and from
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $accountSid = $details['account_sid'] ?? config('services.twilio.account_sid');
            $authToken = $details['auth_token'] ?? config('services.twilio.auth_token');
            $from = $details['from'] ?? config('services.twilio.from');

            if (empty($accountSid) || empty($authToken) || empty($from)) {
                Log::error('Twilio: Missing required credentials');
                return false;
            }

            $to = $data['recipent'] ?? $data['numbers'] ?? '';
            $message = $data['message'] ?? '';

            if (empty($to) || empty($message)) {
                Log::error('Twilio: Missing recipient or message');
                return false;
            }

            // Twilio API endpoint
            $endpoint = "https://api.twilio.com/2010-04-01/Accounts/{$accountSid}/Messages.json";

            $response = Http::withBasicAuth($accountSid, $authToken)
                ->asForm()
                ->post($endpoint, [
                    'From' => $from,
                    'To' => $to,
                    'Body' => $message,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Twilio: Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('Twilio: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

