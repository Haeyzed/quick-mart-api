<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Traits\MailInfo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Class ExportMail
 *
 * Handles sending export files (Excel/PDF) to users.
 * Uses the build() pattern to ensure dynamic MailSetting configuration
 * is applied correctly during queued execution.
 */
class ExportMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, MailInfo;

    /**
     * Create a new message instance.
     *
     * @param User $user The recipient user.
     * @param string $filePath Relative path to the file in storage.
     * @param string $fileName The name of the file to display in email.
     * @param string $subjectLine The email subject.
     * @param GeneralSetting|null $generalSettings Settings for company info.
     * @param MailSetting $mailSetting The mail configuration to use.
     */
    public function __construct(
        public User $user,
        public string $filePath,
        public string $fileName,
        public string $subjectLine,
        public ?GeneralSetting $generalSettings,
        public MailSetting $mailSetting
    ) {}

    /**
     * Build the message.
     *
     * We use build() instead of content/envelope/attachments here because
     * we must execute setMailInfo() to configure the driver before the email is constructed.
     *
     * @return $this
     */
    public function build(): self
    {
        // 1. Apply Dynamic Mail Configuration
        $this->setMailInfo($this->mailSetting);

        // 2. Determine Storage Disk
        $disk = $this->generalSettings?->storage_provider ?? 'public';

        // 3. Build Email with Attachment
        return $this->subject($this->subjectLine)
            ->markdown('emails.exports.generated', [
                'name' => $this->user->name,
                'fileName' => $this->fileName,
                'supportEmail' => $this->generalSettings?->email ?? 'support@example.com',
                'companyName' => $this->generalSettings?->site_title ?? config('app.name'),
            ])
            ->attachFromStorageDisk($disk, $this->filePath, $this->fileName, [
                'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
    }
}
