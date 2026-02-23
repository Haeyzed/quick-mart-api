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
 * ProductBatch Model
 * 
 * Represents a product batch with expiry date for batch tracking.
 *
 * @property int $id
 * @property int $product_id
 * @property string $batch_no
 * @property Carbon|null $expired_date
 * @property float $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Collection<int, ProductSale> $productSales
 * @property-read Collection<int, ProductPurchase> $productPurchases
 * @method static Builder|ProductBatch expired()
 * @method static Builder|ProductBatch expiringSoon(int $days = 30)
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_purchases_count
 * @property-read int|null $product_sales_count
 * @method static Builder<static>|ProductBatch newModelQuery()
 * @method static Builder<static>|ProductBatch newQuery()
 * @method static Builder<static>|ProductBatch query()
 * @method static Builder<static>|ProductBatch whereBatchNo($value)
 * @method static Builder<static>|ProductBatch whereCreatedAt($value)
 * @method static Builder<static>|ProductBatch whereExpiredDate($value)
 * @method static Builder<static>|ProductBatch whereId($value)
 * @method static Builder<static>|ProductBatch whereProductId($value)
 * @method static Builder<static>|ProductBatch whereQty($value)
 * @method static Builder<static>|ProductBatch whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductBatch extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'batch_no',
        'expired_date',
        'qty',
    ];

    /**
     * Get the product for this batch.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the product sales for this batch.
     *
     * @return HasMany<ProductSale>
     */
    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class, 'product_batch_id');
    }

    /**
     * Get the product purchases for this batch.
     *
     * @return HasMany<ProductPurchase>
     */
    public function productPurchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class, 'product_batch_id');
    }

    /**
     * Check if the batch is expiring soon.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expired_date) {
            return false;
        }

        return now()->addDays($days)->isAfter($this->expired_date) && !$this->isExpired();
    }

    /**
     * Check if the batch is expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expired_date) {
            return false;
        }

        return now()->isAfter($this->expired_date);
    }

    /**
     * Scope a query to only include expired batches.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expired_date', '<', now());
    }

    /**
     * Scope a query to only include batches expiring soon.
     */
    public function scopeExpiringSoon(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('expired_date', [now(), now()->addDays($days)]);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'expired_date' => 'date',
            'qty' => 'float',
        ];
    }
}
