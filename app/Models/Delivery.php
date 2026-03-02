<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Delivery
 *
 * Represents a delivery record for a sale. Handles the underlying data
 * structure, relationships, and specific query scopes for delivery entities.
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
 * @method static Builder|Delivery newModelQuery()
 * @method static Builder|Delivery newQuery()
 * @method static Builder|Delivery query()
 * @method static Builder|Delivery pending()
 * @method static Builder|Delivery delivered()
 * @method static Builder|Delivery cancelled()
 * @method static Builder|Delivery filter(array $filters)
 *
 * @property-read \App\Models\Sale $sale
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Courier|null $courier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Delivery customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Delivery last30Days(string $column = 'created_at')
 * @method static Builder<static>|Delivery last7Days(string $column = 'created_at')
 * @method static Builder<static>|Delivery lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Delivery lastYear(string $column = 'created_at')
 * @method static Builder<static>|Delivery monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Delivery quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Delivery today(string $column = 'created_at')
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
 * @method static Builder<static>|Delivery yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Delivery yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Delivery extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sale_id' => 'integer',
        'user_id' => 'integer',
        'courier_id' => 'integer',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                ! empty($filters['sale_id']),
                fn (Builder $q) => $q->where('sale_id', $filters['sale_id'])
            )
            ->when(
                ! empty($filters['courier_id']),
                fn (Builder $q) => $q->where('courier_id', $filters['courier_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('reference_no', 'like', $term)
                        ->orWhere('address', 'like', $term)
                        ->orWhere('delivered_by', 'like', $term)
                        ->orWhere('recieved_by', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

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
}
