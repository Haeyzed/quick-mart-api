<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Delivery Model
 * 
 * Represents a delivery record for a sale.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $sale_id
 * @property string|null $packing_slip_ids
 * @property int $user_id
 * @property string|null $address
 * @property int|null $courier_id
 * @property string|null $delivered_by
 * @property string|null $recieved_by
 * @property string|null $file
 * @property string $status
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sale $sale
 * @property-read User $user
 * @property-read Courier|null $courier
 * @method static Builder|Delivery pending()
 * @method static Builder|Delivery delivered()
 * @method static Builder|Delivery cancelled()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Delivery newModelQuery()
 * @method static Builder<static>|Delivery newQuery()
 * @method static Builder<static>|Delivery query()
 * @method static Builder<static>|Delivery whereAddress($value)
 * @method static Builder<static>|Delivery whereCourierId($value)
 * @method static Builder<static>|Delivery whereCreatedAt($value)
 * @method static Builder<static>|Delivery whereDeliveredBy($value)
 * @method static Builder<static>|Delivery whereFile($value)
 * @method static Builder<static>|Delivery whereId($value)
 * @method static Builder<static>|Delivery whereNote($value)
 * @method static Builder<static>|Delivery wherePackingSlipIds($value)
 * @method static Builder<static>|Delivery whereRecievedBy($value)
 * @method static Builder<static>|Delivery whereReferenceNo($value)
 * @method static Builder<static>|Delivery whereSaleId($value)
 * @method static Builder<static>|Delivery whereStatus($value)
 * @method static Builder<static>|Delivery whereUpdatedAt($value)
 * @method static Builder<static>|Delivery whereUserId($value)
 * @mixin \Eloquent
 */
class Delivery extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'sale_id',
        'packing_slip_ids',
        'user_id',
        'address',
        'courier_id',
        'delivered_by',
        'recieved_by',
        'file',
        'status',
        'note',
    ];

    /**
     * Get the sale for this delivery.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the user who created this delivery.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the courier for this delivery.
     *
     * @return BelongsTo<Courier, self>
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Check if the delivery is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the delivery is delivered.
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Scope a query to only include pending deliveries.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include delivered deliveries.
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope a query to only include cancelled deliveries.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sale_id' => 'integer',
            'user_id' => 'integer',
            'courier_id' => 'integer',
        ];
    }
}
