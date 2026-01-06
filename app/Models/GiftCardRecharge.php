<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * GiftCardRecharge Model
 *
 * Represents a recharge transaction for a gift card.
 *
 * @property int $id
 * @property int $gift_card_id
 * @property float $amount
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read GiftCard $giftCard
 * @property-read User $user
 */
class GiftCardRecharge extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gift_card_recharges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gift_card_id',
        'amount',
        'user_id',
    ];

    /**
     * Get the gift card for this recharge.
     *
     * @return BelongsTo<GiftCard, self>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * Get the user who processed this recharge.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gift_card_id' => 'integer',
            'amount' => 'float',
            'user_id' => 'integer',
        ];
    }
}

