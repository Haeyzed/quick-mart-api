<?php

declare(strict_types=1);

namespace App\Contracts\Sms;

/**
 * Send SMS Interface
 *
 * Defines the contract for SMS sending implementations.
 * All SMS provider classes must implement this interface.
 *
 * @package App\Contracts\Sms
 */
interface SendSmsInterface
{
    /**
     * Send an SMS message.
     *
     * @param array<string, mixed> $data SMS data containing recipient, message, and provider-specific settings
     * @return array<string, mixed>|bool Response from the SMS provider or true/false on success/failure
     */
    public function send(array $data): array|bool;
}

