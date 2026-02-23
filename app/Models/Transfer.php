<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Transfer Model
 * 
 * Represents a stock transfer between warehouses.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property string $status
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $note
 * @property bool $is_sent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Warehouse $fromWarehouse
 * @property-read Warehouse $toWarehouse
 * @property-read Collection<int, ProductTransfer> $productTransfers
 * @method static Builder|Transfer pending()
 * @method static Builder|Transfer completed()
 * @method static Builder|Transfer sent()
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_transfers_count
 * @method static Builder<static>|Transfer newModelQuery()
 * @method static Builder<static>|Transfer newQuery()
 * @method static Builder<static>|Transfer query()
 * @method static Builder<static>|Transfer whereCreatedAt($value)
 * @method static Builder<static>|Transfer whereDocument($value)
 * @method static Builder<static>|Transfer whereFromWarehouseId($value)
 * @method static Builder<static>|Transfer whereGrandTotal($value)
 * @method static Builder<static>|Transfer whereId($value)
 * @method static Builder<static>|Transfer whereIsSent($value)
 * @method static Builder<static>|Transfer whereItem($value)
 * @method static Builder<static>|Transfer whereNote($value)
 * @method static Builder<static>|Transfer whereReferenceNo($value)
 * @method static Builder<static>|Transfer whereShippingCost($value)
 * @method static Builder<static>|Transfer whereStatus($value)
 * @method static Builder<static>|Transfer whereToWarehouseId($value)
 * @method static Builder<static>|Transfer whereTotalCost($value)
 * @method static Builder<static>|Transfer whereTotalQty($value)
 * @method static Builder<static>|Transfer whereTotalTax($value)
 * @method static Builder<static>|Transfer whereUpdatedAt($value)
 * @method static Builder<static>|Transfer whereUserId($value)
 * @mixin \Eloquent
 */
class Transfer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'user_id',
        'status',
        'from_warehouse_id',
        'to_warehouse_id',
        'item',
        'total_qty',
        'total_tax',
        'total_cost',
        'shipping_cost',
        'grand_total',
        'document',
        'note',
        'is_sent',
    ];

    /**
     * Get the user who created this transfer.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the source warehouse for this transfer.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the destination warehouse for this transfer.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the product transfers for this transfer.
     *
     * @return HasMany<ProductTransfer>
     */
    public function productTransfers(): HasMany
    {
        return $this->hasMany(ProductTransfer::class);
    }

    /**
     * Scope a query to only include pending transfers.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed transfers.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include sent transfers.
     */
    public function scopeSent(Builder $query): Builder
    {
        return $query->where('is_sent', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'from_warehouse_id' => 'integer',
            'to_warehouse_id' => 'integer',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_tax' => 'float',
            'total_cost' => 'float',
            'shipping_cost' => 'float',
            'grand_total' => 'float',
            'is_sent' => 'boolean',
        ];
    }
}
