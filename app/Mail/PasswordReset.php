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
 * PasswordReset Mailable
 *
 * Email notification sent when a user requests a password reset.
 */
class PasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $token
     * @param GeneralSetting|null $generalSetting
     */
    public function __construct(
        public readonly User            $user,
        public readonly string          $token,
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

        // Generate password reset URL using frontend URL
        $frontendUrl = config('app.frontend_url', config('app.url'));
        $resetUrl = $frontendUrl . '/reset-password?' . http_build_query([
            'token' => $this->token,
            'email' => $this->user->email,
        ]);

        return $this->view('emails.password-reset', [
            'user' => $this->user,
            'resetUrl' => $resetUrl,
            'token' => $this->token,
            'generalSetting' => $generalSetting,
        ])
            ->subject('Reset Your Password');
    }
}

