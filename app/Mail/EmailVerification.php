<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;

/**
 * EmailVerification Mailable
 *
 * Email notification sent when a user needs to verify their email address.
 */
class EmailVerification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param GeneralSetting|null $generalSetting
     */
    public function __construct(
        public readonly User            $user,
        public readonly ?GeneralSetting $generalSetting = null
    )
    {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        $generalSetting = $this->generalSetting ?? \App\Models\GeneralSetting::latest()->first();

        // Generate verification URL for API
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->user->getKey(), 'hash' => sha1($this->user->getEmailForVerification())],
            absolute: true
        );

        return $this->view('emails.email-verification', [
            'user' => $this->user,
            'verificationUrl' => $verificationUrl,
            'generalSetting' => $generalSetting,
        ])
            ->subject('Verify Your Email Address');
    }
}

