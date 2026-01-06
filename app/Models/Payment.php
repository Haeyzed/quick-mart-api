<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Payment Model
 *
 * Represents a payment transaction for a sale or purchase.
 *
 * @property int $id
 * @property int|null $purchase_id
 * @property int $user_id
 * @property int|null $sale_id
 * @property int|null $cash_register_id
 * @property int|null $account_id
 * @property string|null $payment_receiver
 * @property string|null $payment_reference
 * @property float $amount
 * @property int|null $currency_id
 * @property int|null $installment_id
 * @property float|null $exchange_rate
 * @property Carbon $payment_at
 * @property float|null $used_points
 * @property float|null $change
 * @property string $paying_method
 * @property string|null $payment_proof
 * @property string|null $document
 * @property string|null $payment_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Purchase|null $purchase
 * @property-read Sale|null $sale
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Installment|null $installment
 * @property-read PaymentWithCheque|null $cheque
 * @property-read PaymentWithCreditCard|null $creditCard
 * @property-read PaymentWithGiftCard|null $giftCard
 * @property-read PaymentWithPaypal|null $paypal
 *
 * @method static Builder|Payment byMethod(string $method)
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'purchase_id',
        'user_id',
        'sale_id',
        'cash_register_id',
        'account_id',
        'payment_receiver',
        'payment_reference',
        'amount',
        'currency_id',
        'installment_id',
        'exchange_rate',
        'payment_at',
        'used_points',
        'change',
        'paying_method',
        'payment_proof',
        'document',
        'payment_note',
    ];

    /**
     * Get the purchase for this payment.
     *
     * @return BelongsTo<Purchase, self>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the sale for this payment.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user who processed this payment.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cash register for this payment.
     *
     * @return BelongsTo<CashRegister, self>
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Get the account for this payment.
     *
     * @return BelongsTo<Account, self>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the currency for this payment.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the installment for this payment.
     *
     * @return BelongsTo<Installment, self>
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    /**
     * Get the cheque payment details.
     *
     * @return HasOne<PaymentWithCheque>
     */
    public function cheque(): HasOne
    {
        return $this->hasOne(PaymentWithCheque::class);
    }

    /**
     * Get the credit card payment details.
     *
     * @return HasOne<PaymentWithCreditCard>
     */
    public function creditCard(): HasOne
    {
        return $this->hasOne(PaymentWithCreditCard::class);
    }

    /**
     * Get the gift card payment details.
     *
     * @return HasOne<PaymentWithGiftCard>
     */
    public function giftCard(): HasOne
    {
        return $this->hasOne(PaymentWithGiftCard::class);
    }

    /**
     * Get the PayPal payment details.
     *
     * @return HasOne<PaymentWithPaypal>
     */
    public function paypal(): HasOne
    {
        return $this->hasOne(PaymentWithPaypal::class);
    }

    /**
     * Scope a query to filter by payment method.
     *
     * @param Builder $query
     * @param string $method
     * @return Builder
     */
    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('paying_method', $method);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_id' => 'integer',
            'user_id' => 'integer',
            'sale_id' => 'integer',
            'cash_register_id' => 'integer',
            'account_id' => 'integer',
            'amount' => 'float',
            'currency_id' => 'integer',
            'installment_id' => 'integer',
            'exchange_rate' => 'float',
            'payment_at' => 'datetime',
            'used_points' => 'float',
            'change' => 'float',
        ];
    }
}

