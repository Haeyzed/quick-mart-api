<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

/**
 * ExportMail Mailable
 *
 * Email notification sent when exporting data.
 */
class ExportMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $filePath
     * @param string $fileName
     * @param string $type
     * @param GeneralSetting|null $generalSetting
     */
    public function __construct(
        public readonly User            $user,
        public readonly string          $filePath,
        public readonly string          $fileName,
        public readonly string          $type,
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

        $mail = $this->view('emails.export', [
            'user' => $this->user,
            'fileName' => $this->fileName,
            'type' => $this->type,
            'generalSetting' => $generalSetting,
        ])
            ->subject("{$this->type} Export - {$this->fileName}")
            ->attach(Storage::disk('public')->path($this->filePath), [
                'as' => $this->fileName,
            ]);

        return $mail;
    }
}

