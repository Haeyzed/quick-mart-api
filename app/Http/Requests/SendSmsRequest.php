<?php

declare(strict_types=1);

namespace App\Http\Requests;

/**
 * Form request for sending SMS.
 *
 * Validates recipient, message (or template_id with placeholders), and optional placeholders.
 */
class SendSmsRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:50'],
            'message' => ['required_without:template_id', 'nullable', 'string', 'max:1600'],
            'template_id' => ['nullable', 'integer', 'exists:sms_templates,id'],
            'customer' => ['nullable', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'sale_status' => ['nullable', 'string', 'max:50'],
            'payment_status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
