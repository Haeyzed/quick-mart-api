<?php

declare(strict_types=1);

namespace App\ViewModels;

use App\Models\Customer;
use App\Models\ExternalService;
use App\Models\SmsTemplate;
use App\Services\SmsService;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * SMS Model
 *
 * Handles SMS processing, template replacement, and provider initialization.
 * This class processes SMS data, replaces placeholders in templates,
 * and delegates actual sending to the SmsService.
 *
 * @package App\ViewModels
 */
class SmsModel implements ISmsModel
{
    /**
     * Create a new SMS Model instance.
     *
     * @param SmsService $smsService The SMS service instance for sending messages
     */
    public function __construct(
        private readonly SmsService $smsService
    )
    {
    }

    /**
     * Initialize and process SMS sending.
     *
     * @param array<string, mixed> $data SMS data containing:
     *   - 'type': string - Type of SMS (onsite/online)
     *   - 'template_id': int - ID of the SMS template
     *   - 'customer_id': int|array - Customer ID or customer data
     *   - 'reference_no': string - Reference number for the transaction
     *   - 'sale_status': string|int - Sale status
     *   - 'payment_status': string|int - Payment status
     * @return void
     */
    public function initialize(array $data): void
    {
        try {
            // Normalize sale status
            $saleStatus = $this->normalizeSaleStatus($data['sale_status'] ?? '');

            // Normalize payment status
            $paymentStatus = $this->normalizePaymentStatus($data['payment_status'] ?? '');

            $smsData = $this->processSmsData(
                $data['type'] ?? '',
                (int)($data['template_id'] ?? 0),
                $data['customer_id'] ?? null,
                $data['reference_no'] ?? '',
                $saleStatus,
                $paymentStatus
            );

            if (!empty($smsData)) {
                $this->smsService->initialize($smsData);
            }
        } catch (Exception $e) {
            Log::error('SmsModel: Exception while initializing SMS', [
                'message' => $e->getMessage(),
                'data' => $data,
            ]);
        }
    }

    /**
     * Normalize sale status from numeric code to string.
     *
     * @param string|int $status Sale status code
     * @return string Normalized sale status
     */
    private function normalizeSaleStatus(string|int $status): string
    {
        return match ((string)$status) {
            '1' => 'completed',
            '2' => 'pending',
            '3' => 'draft',
            '4' => 'returned',
            default => (string)$status,
        };
    }

    /**
     * Normalize payment status from numeric code to string.
     *
     * @param string|int $status Payment status code
     * @return string Normalized payment status
     */
    private function normalizePaymentStatus(string|int $status): string
    {
        return match ((string)$status) {
            '1' => 'pending',
            '2' => 'due',
            '3' => 'partial',
            '4' => 'paid',
            default => (string)$status,
        };
    }

    /**
     * Process SMS data and prepare it for sending.
     *
     * @param string $type Type of SMS (onsite/online)
     * @param int $templateId SMS template ID
     * @param int|array|null $customerData Customer ID or customer data array
     * @param string $referenceNo Reference number
     * @param string $saleStatus Normalized sale status
     * @param string $paymentStatus Normalized payment status
     * @return array<string, mixed> Processed SMS data ready for sending
     */
    private function processSmsData(
        string         $type,
        int            $templateId,
        int|array|null $customerData,
        string         $referenceNo,
        string         $saleStatus,
        string         $paymentStatus
    ): array
    {
        $smsTemplate = SmsTemplate::find($templateId);

        if (!$smsTemplate) {
            Log::warning('SmsModel: Template not found', ['template_id' => $templateId]);
            return [];
        }

        $template = $smsTemplate->content;
        $customerName = '';
        $recipient = '';

        if ($type === 'onsite' && is_int($customerData)) {
            $customer = Customer::find($customerData);
            if ($customer) {
                $customerName = $customer->name;
                $recipient = $customer->phone_number ?? '';
            }
        } elseif ($type === 'online' && is_array($customerData)) {
            $customerName = $customerData['billing_name'] ?? '';
            $recipient = $customerData['billing_phone'] ?? '';
        }

        $smsData = [
            'recipent' => $recipient,
            'message' => $this->replacePlaceholders($template, $customerName, $referenceNo, $saleStatus, $paymentStatus),
        ];

        $smsProvider = ExternalService::where('active', true)
            ->where('type', 'sms')
            ->first();

        if ($smsProvider) {
            $smsData['sms_provider_name'] = $smsProvider->name;
            $smsData['details'] = $smsProvider->details;
        }

        return $smsData;
    }

    /**
     * Replace placeholders in SMS template with actual values.
     *
     * @param string $template SMS template with placeholders
     * @param string $customerName Customer name
     * @param string $referenceNo Reference number
     * @param string $saleStatus Sale status
     * @param string $paymentStatus Payment status
     * @return string Template with placeholders replaced
     */
    private function replacePlaceholders(
        string $template,
        string $customerName,
        string $referenceNo,
        string $saleStatus,
        string $paymentStatus
    ): string
    {
        $replacements = [
            '[customer]' => $customerName,
            '[reference]' => $referenceNo,
            '[sale_status]' => $saleStatus,
            '[payment_status]' => $paymentStatus,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}

