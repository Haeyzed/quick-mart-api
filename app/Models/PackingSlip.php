<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * PackingSlip Model
 * 
 * Represents a packing slip for a sale delivery.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $sale_id
 * @property int|null $delivery_id
 * @property float $amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sale $sale
 * @property-read Delivery|null $delivery
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, PackingSlipProduct> $packingSlipProducts
 * @method static Builder|PackingSlip pending()
 * @method static Builder|PackingSlip completed()
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $packing_slip_products_count
 * @property-read int|null $products_count
 * @method static Builder<static>|PackingSlip newModelQuery()
 * @method static Builder<static>|PackingSlip newQuery()
 * @method static Builder<static>|PackingSlip query()
 * @method static Builder<static>|PackingSlip whereAmount($value)
 * @method static Builder<static>|PackingSlip whereCreatedAt($value)
 * @method static Builder<static>|PackingSlip whereDeliveryId($value)
 * @method static Builder<static>|PackingSlip whereId($value)
 * @method static Builder<static>|PackingSlip whereReferenceNo($value)
 * @method static Builder<static>|PackingSlip whereSaleId($value)
 * @method static Builder<static>|PackingSlip whereStatus($value)
 * @method static Builder<static>|PackingSlip whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PackingSlip extends Model implements AuditableContract
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
        'delivery_id',
        'amount',
        'status',
    ];

    /**
     * Get the sale for this packing slip.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the delivery for this packing slip.
     *
     * @return BelongsTo<Delivery, self>
     */
    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    /**
     * Get the products in this packing slip.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'packing_slip_products')
            ->withPivot('variant_id')
            ->withTimestamps();
    }

    /**
     * Get the packing slip products (pivot records).
     *
     * @return HasMany<PackingSlipProduct>
     */
    public function packingSlipProducts(): HasMany
    {
        return $this->hasMany(PackingSlipProduct::class);
    }

    /**
     * Scope a query to only include pending packing slips.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed packing slips.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
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
            'delivery_id' => 'integer',
            'amount' => 'float',
        ];
    }
}
