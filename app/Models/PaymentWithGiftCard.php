<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * PaymentWithGiftCard Model
 *
 * Represents gift card payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property int $gift_card_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Payment $payment
 * @property-read GiftCard $giftCard
 */
class PaymentWithGiftCard extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_with_gift_card';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'gift_card_id',
    ];

    /**
     * Get the payment that owns this gift card payment.
     *
     * @return BelongsTo<Payment, self>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the gift card used for this payment.
     *
     * @return BelongsTo<GiftCard, self>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'payment_id' => 'integer',
            'gift_card_id' => 'integer',
        ];
    }
}

