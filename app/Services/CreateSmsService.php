<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExternalService;
use App\Models\SmsTemplate;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for Create/Send SMS operations.
 *
 * Centralizes business logic for listing SMS templates and sending SMS via active provider.
 */
class CreateSmsService extends BaseService
{
    use CheckPermissionsTrait;

    private const SMS_TYPE = 'sms';

    /**
     * CreateSmsService constructor.
     *
     * @param SmsService $smsService Handles SMS delivery via configured provider.
     */
    public function __construct(
        private readonly SmsService $smsService
    ) {}

    /**
     * List SMS templates.
     */
    public function getTemplates(): Collection
    {
        $this->requirePermission('create_sms');

        return SmsTemplate::orderBy('name')->get();
    }

    /**
     * Send SMS directly or from template.
     *
     * @param array<string, mixed> $data recipient, message, optional template_id and placeholders.
     * @return array<string, mixed>|bool Provider response or false.
     */
    public function sendSms(array $data): array|bool
    {
        $this->requirePermission('create_sms');

        $recipient = trim((string) ($data['recipient'] ?? ''));
        $message = $data['message'] ?? '';
        $templateId = isset($data['template_id']) ? (int) $data['template_id'] : null;

        if (empty($recipient)) {
            throw new \InvalidArgumentException('Recipient phone number is required.');
        }

        if ($templateId) {
            $message = $this->buildMessageFromTemplate($templateId, $data);
        }

        if (empty($message)) {
            throw new \InvalidArgumentException('Message content is required.');
        }

        $provider = ExternalService::where('type', self::SMS_TYPE)->where('active', true)->first();

        if (! $provider) {
            throw new \RuntimeException('No active SMS provider configured. Please configure SMS settings first.');
        }

        $smsData = [
            'recipent' => $recipient,
            'message' => $message,
            'sms_provider_name' => $provider->name,
            'details' => $provider->details,
        ];

        return $this->smsService->initialize($smsData);
    }

    /**
     * Build message from template with optional placeholders.
     *
     * @param array<string, mixed> $data Placeholders: customer, reference, sale_status, payment_status.
     */
    private function buildMessageFromTemplate(int $templateId, array $data): string
    {
        $template = SmsTemplate::find($templateId);

        if (! $template) {
            throw new \InvalidArgumentException('SMS template not found.');
        }

        $replacements = [
            '[customer]' => (string) ($data['customer'] ?? ''),
            '[reference]' => (string) ($data['reference'] ?? ''),
            '[sale_status]' => (string) ($data['sale_status'] ?? ''),
            '[payment_status]' => (string) ($data['payment_status'] ?? ''),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template->content);
    }
}
