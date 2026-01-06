<?php

declare(strict_types=1);

namespace App\Services;

use App\SMSProviders\BdBulkSms;
use App\SMSProviders\Clickatell;
use App\SMSProviders\ReveSms;
use App\SMSProviders\SmsToday;
use App\SMSProviders\TonkraSms;
use App\SMSProviders\Twilio;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * SMS Service
 *
 * Service for sending SMS messages through various SMS providers.
 * This service acts as a factory and router for different SMS provider implementations.
 *
 * @package App\Services
 */
class SmsService
{
    /**
     * Create a new SMS Service instance.
     *
     * @param TonkraSms $tonkraSms Tonkra SMS provider instance
     * @param ReveSms $reveSms Reve SMS provider instance
     * @param BdBulkSms $bdBulkSms BD Bulk SMS provider instance
     * @param Clickatell $clickatell Clickatell SMS provider instance
     * @param SmsToday $smsToday SMS Today provider instance
     * @param Twilio $twilio Twilio SMS provider instance
     */
    public function __construct(
        private readonly TonkraSms  $tonkraSms,
        private readonly ReveSms    $reveSms,
        private readonly BdBulkSms  $bdBulkSms,
        private readonly Clickatell $clickatell,
        private readonly SmsToday   $smsToday,
        private readonly Twilio     $twilio
    )
    {
    }

    /**
     * Initialize and send SMS through the appropriate provider.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'sms_provider_name': string - Name of the SMS provider (tonkra, revesms, bdbulksms)
     *   - 'recipent': string - Recipient phone number
     *   - 'message': string - SMS message content
     *   - 'details': mixed - Provider-specific configuration details
     * @return array<string, mixed>|bool Response from the SMS provider or false on failure
     */
    public function initialize(array $data): array|bool
    {
        $providerName = strtolower($data['sms_provider_name'] ?? '');

        try {
            return match ($providerName) {
                'tonkra' => $this->tonkraSms->send($data),
                'revesms' => $this->reveSms->send($data),
                'bdbulksms' => $this->bdBulkSms->send($data),
                'clickatell' => $this->clickatell->send($data),
                'smstoday' => $this->smsToday->send($data),
                'twilio' => $this->twilio->send($data),
                default => $this->handleUnknownProvider($providerName),
            };
        } catch (Exception $e) {
            Log::error('SmsService: Exception while sending SMS', [
                'provider' => $providerName,
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle unknown SMS provider.
     *
     * @param string $providerName Name of the unknown provider
     * @return bool Always returns false
     */
    private function handleUnknownProvider(string $providerName): bool
    {
        Log::warning('SmsService: Unknown SMS provider', [
            'provider' => $providerName,
        ]);

        return false;
    }
}

