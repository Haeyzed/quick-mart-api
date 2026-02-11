<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * GiftCard Model
 *
 * Represents a gift card that can be used for payments.
 *
 * @property int $id
 * @property string $card_no
 * @property float $amount
 * @property float $expense
 * @property int|null $customer_id
 * @property int|null $user_id
 * @property Carbon|null $expired_date
 * @property int|null $created_by
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer|null $customer
 * @property-read User|null $user
 * @property-read User|null $creator
 * @property-read Collection<int, PaymentWithGiftCard> $payments
 *
 * @method static Builder|GiftCard active()
 * @method static Builder|GiftCard expired()
 * @method static Builder|GiftCard notExpired()
 */
class GiftCard extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'card_no',
        'amount',
        'expense',
        'customer_id',
        'user_id',
        'expired_date',
        'created_by',
        'is_active',
    ];

    /**
     * Generate a unique 16-digit numeric gift card code.
     */
    public static function generateCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 9999999999999999), 16, '0', STR_PAD_LEFT);
        } while (self::where('card_no', $code)->where('is_active', true)->exists());

        return $code;
    }

    /**
     * Get the customer for this gift card.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user for this gift card.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who created this gift card.
     *
     * @return BelongsTo<User, self>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the payments made with this gift card.
     *
     * @return HasMany<PaymentWithGiftCard>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(PaymentWithGiftCard::class);
    }

    /**
     * Check if the gift card is expired.
     */
    public function isExpired(): bool
    {
        return $this->expired_date && now()->isAfter($this->expired_date);
    }

    /**
     * Check if the gift card has balance.
     */
    public function hasBalance(): bool
    {
        return $this->getRemainingBalance() > 0;
    }

    /**
     * Get the remaining balance on this gift card.
     */
    public function getRemainingBalance(): float
    {
        return max(0, $this->amount - $this->expense);
    }

    /**
     * Scope a query to only include active gift cards.
     *
     * @param  Builder  $query
     * @return Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include expired gift cards.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_date', '<', now());
    }

    /**
     * Scope a query to only include non-expired gift cards.
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereNull('expired_date')
                ->orWhere('expired_date', '>', now());
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'expense' => 'float',
            'customer_id' => 'integer',
            'user_id' => 'integer',
            'expired_date' => 'date',
            'created_by' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
