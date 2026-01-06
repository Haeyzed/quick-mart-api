<?php

declare(strict_types=1);

namespace App\SMSProviders;

use App\Contracts\Sms\CheckBalanceInterface;
use App\Contracts\Sms\SendSmsInterface;
use App\Models\ExternalService;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Tonkra SMS Provider
 *
 * Implementation of SMS sending and balance checking for Tonkra SMS service.
 *
 * @package App\SMSProviders
 */
class TonkraSms implements SendSmsInterface, CheckBalanceInterface
{
    private const API_BASE_URL = 'https://sms.tonkra.com/api/v3';
    private const SMS_SEND_ENDPOINT = '/sms/send';
    private const BALANCE_ENDPOINT = '/balance';

    /**
     * Send an SMS message via Tonkra SMS service.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'recipent': string - Recipient phone number
     *   - 'message': string - SMS message content
     *   - 'details': object|array - Provider details containing api_token and sender_id
     * @return array<string, mixed>|bool Response from the API or false on failure
     */
    public function send(array $data): array|bool
    {
        try {
            $details = is_string($data['details'] ?? null)
                ? json_decode($data['details'], true)
                : (array)($data['details'] ?? []);

            $apiToken = $details['api_token'] ?? '';
            $senderId = $details['sender_id'] ?? '';

            if (empty($apiToken) || empty($senderId)) {
                Log::error('TonkraSms: Missing API token or sender ID');
                return false;
            }

            $params = [
                'recipient' => $data['recipent'] ?? '',
                'sender_id' => $senderId,
                'type' => 'plain',
                'message' => $data['message'] ?? '',
            ];

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiToken}",
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ])->post(self::API_BASE_URL . self::SMS_SEND_ENDPOINT, $params);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('TonkraSms: Failed to send SMS', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('TonkraSms: Exception while sending SMS', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Check the remaining balance in the Tonkra SMS account.
     *
     * @return float|int The remaining balance, or 0 if unable to retrieve
     */
    public function balance(): float|int
    {
        try {
            $tonkra = ExternalService::where('name', 'tonkra')->first();

            if (!$tonkra) {
                return 0;
            }

            $details = is_string($tonkra->details)
                ? json_decode($tonkra->details, true)
                : (array)($tonkra->details ?? []);

            $apiToken = $details['api_token'] ?? '';

            if (empty($apiToken)) {
                return 0;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->get(self::API_BASE_URL . self::BALANCE_ENDPOINT);

            if ($response->successful()) {
                $responseData = $response->json();
                $balance = $responseData['data']['remaining_balance'] ?? '0';

                // Extract numeric value from balance string
                $balance = preg_replace("/[^0-9.]/", "", (string)$balance);

                return (float)$balance;
            }

            return 0;
        } catch (Exception $e) {
            Log::error('TonkraSms: Exception while checking balance', [
                'message' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}

