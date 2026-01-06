<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\GiftCard;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * GiftCardRecharge Mailable
 *
 * Email notification sent when a gift card is recharged.
 */
class GiftCardRecharge extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param GiftCard $giftCard
     * @param string $name Recipient name
     * @param float $rechargeAmount Amount recharged
     * @param GeneralSetting|null $generalSetting
     */
    public function __construct(
        public readonly GiftCard        $giftCard,
        public readonly string          $name,
        public readonly float           $rechargeAmount,
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
        // Load relationships if needed
        if ($this->giftCard->user_id && !$this->giftCard->relationLoaded('user')) {
            $this->giftCard->load('user');
        }
        if ($this->giftCard->customer_id && !$this->giftCard->relationLoaded('customer')) {
            $this->giftCard->load('customer');
        }

        $generalSetting = $this->generalSetting ?? GeneralSetting::latest()->first();
        $dateFormat = $generalSetting?->date_format ?? 'Y-m-d';

        return $this->view('emails.gift-card-recharge', [
            'giftCard' => $this->giftCard,
            'name' => $this->name,
            'rechargeAmount' => $this->rechargeAmount,
            'dateFormat' => $dateFormat,
            'generalSetting' => $generalSetting,
        ])
            ->subject('GiftCard Recharge Info');
    }
}

