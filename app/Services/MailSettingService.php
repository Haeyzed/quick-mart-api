<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MailSetting;
use App\Traits\MailInfo;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Class MailSettingService
 *
 * Handles all core business logic and database interactions for Mail Settings.
 * Acts as the intermediary between the controllers and the database layer.
 */
class MailSettingService extends BaseService
{
    use MailInfo;

    /**
     * MailSettingService constructor.
     */
    public function __construct() {}

    /**
     * Retrieve the mail setting (latest).
     *
     * @return MailSetting|null The latest mail setting instance.
     */
    public function getMailSetting(): ?MailSetting
    {
        return MailSetting::latest()->first();
    }

    /**
     * Update the mail setting and optionally send test email.
     *
     * Updates the mail setting record within a database transaction.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @param  bool  $sendTest  If true, sends test email to from_address.
     * @return MailSetting The freshly updated MailSetting model instance.
     */
    public function updateMailSetting(array $data, bool $sendTest = false): MailSetting
    {
        return DB::transaction(function () use ($data, $sendTest) {
            $mailSetting = MailSetting::latest()->first() ?? new MailSetting();

            if (!empty($data['password'])) {
                $data['password'] = trim((string)$data['password']);
            } else {
                unset($data['password']);
            }

            $mailSetting->fill($data)->save();

            if ($sendTest) {
                $this->sendTestTo($mailSetting);
            }

            return $mailSetting->fresh();
        });
    }

    /**
     * Send a test email using current mail settings.
     */
    public function sendTestEmail(): void
    {
        $mailSetting = MailSetting::default()->first() ?? MailSetting::latest()->first();

        if (!$mailSetting) {
            throw new RuntimeException('Mail settings are not configured.');
        }

        $this->sendTestTo($mailSetting);
    }

    /**
     * Internal helper to dispatch test email.
     *
     * @param  MailSetting  $setting
     */
    private function sendTestTo(MailSetting $setting): void
    {
        $this->setMailInfo($setting);
        Mail::raw('SMTP configuration test successful.', function ($message) use ($setting) {
            $message->to($setting->from_address)->subject('Test Mail');
        });
    }
}
