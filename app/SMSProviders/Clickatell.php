<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\SendSmsInterface;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Clickatell SMS Provider
 *
 * Implementation of SMS sending for Clickatell SMS service.
 * Note: Requires clickatell/clickatell-php package for full functionality.
 *
 * @package App\SMSProviders
 */
class Clickatell implements SendSmsInterface
{
    /**
     * Send an SMS message via Clickatell SMS service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'numbers': array<string> - Array of recipient phone numbers
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing api_key
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $apiKey = $details['api_key'] ?? config('services.clickatell.api_key');

            if (empty($apiKey)) {
                Log::error('Clickatell: Missing API key');
                return false;
            }

            $numbers = $data['numbers'] ?? [];
            $message = $data['message'] ?? '';

            if (empty($numbers) || empty($message)) {
                Log::error('Clickatell: Missing numbers or message');
                return false;
            }

            // Clickatell REST API endpoint
            $endpoint = 'https://platform.clickatell.com/messages/http/send';

            $results = [];
            foreach ($numbers as $number) {
                $response = Http::withHeaders([
                    'Authorization' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->post($endpoint, [
                    'to' => [$number],
                    'content' => $message,
                ]);

                if ($response->successful()) {
                    $results[] = $response->json();
                } else {
                    Log::error('Clickatell: Failed to send SMS', [
                        'number' => $number,
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);
                }
            }

            return !empty($results) ? ['results' => $results] : false;
        } catch (Exception $e) {
            Log::error('Clickatell: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }
}

