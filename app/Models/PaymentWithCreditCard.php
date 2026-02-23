<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * PaymentWithCreditCard Model
 * 
 * Represents credit card payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property int|null $customer_id
 * @property string|null $customer_stripe_id
 * @property string|null $charge_id
 * @property string|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereChargeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCustomerStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PaymentWithCreditCard extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_with_credit_card';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'customer_id',
        'customer_stripe_id',
        'charge_id',
        'data',
    ];

    /**
     * Get the payment that owns this credit card payment.
     *
     * @return BelongsTo<Payment, self>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the customer for this credit card payment.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
            'customer_id' => 'integer',
        ];
    }
}
