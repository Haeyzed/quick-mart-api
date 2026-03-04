<?php

declare(strict_types=1);

namespace App\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * PaymentWithCheque Model
 * 
 * Represents cheque payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property string $cheque_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|PaymentWithCheque newModelQuery()
 * @method static Builder<static>|PaymentWithCheque newQuery()
 * @method static Builder<static>|PaymentWithCheque query()
 * @method static Builder<static>|PaymentWithCheque whereChequeNo($value)
 * @method static Builder<static>|PaymentWithCheque whereCreatedAt($value)
 * @method static Builder<static>|PaymentWithCheque whereId($value)
 * @method static Builder<static>|PaymentWithCheque wherePaymentId($value)
 * @method static Builder<static>|PaymentWithCheque whereUpdatedAt($value)
 * @mixin Eloquent
 */
class PaymentWithCheque extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payment_with_cheque';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payment_id',
        'cheque_no',
    ];

    /**
     * Get the payment that owns this cheque payment.
     *
     * @return BelongsTo<Payment, self>
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
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
        ];
    }
}
