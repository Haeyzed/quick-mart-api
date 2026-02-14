<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\MailSetting;

/**
 * Mail Info Trait
 *
 * Provides methods to configure mail settings dynamically.
 * This trait allows runtime configuration of mail settings from the database.
 *
 * @package App\Traits
 */
trait MailInfo
{
    /**
     * Set mail configuration from MailSetting model.
     *
     * This method updates the application's mail configuration
     * at runtime based on settings stored in the database.
     *
     * @param MailSetting $mailSetting The mail setting model instance
     * @return void
     */
    public function setMailInfo(MailSetting $mailSetting): void
    {
        config()->set('mail.default', $mailSetting->driver);
        config()->set('mail.mailers.smtp.host', $mailSetting->host);
        config()->set('mail.mailers.smtp.port', $mailSetting->port);
        config()->set('mail.mailers.smtp.encryption', $mailSetting->encryption);
        config()->set('mail.mailers.smtp.username', $mailSetting->username);
        config()->set('mail.mailers.smtp.password', $mailSetting->password);
        config()->set('mail.from.address', $mailSetting->from_address);
        config()->set('mail.from.name', $mailSetting->from_name);
    }
}

