<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

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
 *
 * @property-read Sale $sale
 * @property-read User $user
 * @property-read Courier|null $courier
 *
 * @method static Builder|Delivery pending()
 * @method static Builder|Delivery delivered()
 * @method static Builder|Delivery cancelled()
 */
class Delivery extends Model
{
    use HasFactory;

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
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the delivery is delivered.
     *
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    /**
     * Scope a query to only include pending deliveries.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include delivered deliveries.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeDelivered(Builder $query): Builder
    {
        return $query->where('status', 'delivered');
    }

    /**
     * Scope a query to only include cancelled deliveries.
     *
     * @param Builder $query
     * @return Builder
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

