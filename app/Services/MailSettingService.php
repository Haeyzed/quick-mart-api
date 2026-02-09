<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MailSetting;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Illuminate\Support\Facades\Mail;

/**
 * Service class for Mail Setting operations.
 *
 * Centralizes business logic for SMTP configuration and test email.
 */
class MailSettingService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * MailSettingService constructor.
     */
    public function __construct() {}

    /**
     * Retrieve the mail setting (latest).
     *
     * Requires mail_setting permission.
     *
     * @return MailSetting|null The latest mail setting or null if not configured.
     */
    public function getMailSetting(): ?MailSetting
    {
        $this->requirePermission('mail_setting');

        return MailSetting::latest()->first();
    }

    /**
     * Update the mail setting and optionally send test email.
     *
     * Requires mail_setting permission.
     *
     * @param  array<string, mixed>  $data  Validated data.
     * @param  bool  $sendTest  If true, sends test email to from_address.
     * @return MailSetting The updated mail setting instance.
     *
     * @throws \Exception When sending test email fails.
     */
    public function updateMailSetting(array $data, bool $sendTest = false): MailSetting
    {
        $this->requirePermission('mail_setting');

        $mailSetting = MailSetting::latest()->first();

        if (! $mailSetting) {
            $mailSetting = new MailSetting;
        }

        if (! empty($data['password'])) {
            $data['password'] = trim((string) $data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['send_test']);

        $mailSetting->fill($data)->save();

        if ($sendTest) {
            $this->setMailInfo($mailSetting);
            Mail::raw('This is a test mail to confirm your SMTP settings are working.', function ($message) use ($mailSetting) {
                $message->to($mailSetting->from_address)
                    ->subject('Test Mail');
            });
        }

        return $mailSetting->fresh();
    }

    /**
     * Send a test email using current mail settings.
     *
     * Requires mail_setting permission.
     *
     * @throws \Exception When mail settings are missing or sending fails.
     */
    public function sendTestEmail(): void
    {
        $this->requirePermission('mail_setting');

        $mailSetting = MailSetting::default()->first()
            ?? MailSetting::latest()->first();

        if (! $mailSetting) {
            throw new \RuntimeException('Mail settings are not configured.');
        }

        $this->setMailInfo($mailSetting);
        Mail::raw('This is a test mail to confirm your SMTP settings are working.', function ($message) use ($mailSetting) {
            $message->to($mailSetting->from_address)
                ->subject('Test Mail');
        });
    }
}
