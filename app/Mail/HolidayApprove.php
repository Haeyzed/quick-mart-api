<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\Holiday;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * HolidayApprove Mailable
 *
 * Email notification sent when a holiday request is approved.
 */
class HolidayApprove extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Holiday $holiday The approved holiday instance
     * @param GeneralSetting|null $generalSetting General settings for date formatting
     */
    public function __construct(
        public readonly Holiday         $holiday,
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
        // Ensure user relationship is loaded
        if (!$this->holiday->relationLoaded('user')) {
            $this->holiday->load('user');
        }

        $generalSetting = $this->generalSetting ?? GeneralSetting::latest()->first();
        $dateFormat = $generalSetting?->date_format ?? 'Y-m-d';

        return $this->view('emails.holiday-approve', [
            'holiday' => $this->holiday,
            'user' => $this->holiday->user,
            'dateFormat' => $dateFormat,
            'generalSetting' => $generalSetting,
        ])
            ->subject('Holiday Request Approved');
    }
}

