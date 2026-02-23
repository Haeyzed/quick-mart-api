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
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Purchase Model
 * 
 * Represents a purchase transaction in the system.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $warehouse_id
 * @property int $supplier_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float|null $order_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property float $paid_amount
 * @property string $status
 * @property string $payment_status
 * @property string|null $document
 * @property string|null $note
 * @property string|null $purchase_type
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read Currency|null $currency
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, ProductPurchase> $productPurchases
 * @property-read Collection<int, ReturnPurchase> $returns
 * @property-read Collection<int, Payment> $payments
 * @property-read InstallmentPlan|null $installmentPlan
 * @property-read User|null $deleter
 * @method static Builder|Purchase completed()
 * @method static Builder|Purchase pending()
 * @method static Builder|Purchase paid()
 * @method static Builder|Purchase unpaid()
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $payments_count
 * @property-read int|null $product_purchases_count
 * @property-read int|null $products_count
 * @property-read int|null $returns_count
 * @method static Builder<static>|Purchase newModelQuery()
 * @method static Builder<static>|Purchase newQuery()
 * @method static Builder<static>|Purchase onlyTrashed()
 * @method static Builder<static>|Purchase query()
 * @method static Builder<static>|Purchase whereCreatedAt($value)
 * @method static Builder<static>|Purchase whereCurrencyId($value)
 * @method static Builder<static>|Purchase whereDeletedAt($value)
 * @method static Builder<static>|Purchase whereDeletedBy($value)
 * @method static Builder<static>|Purchase whereDocument($value)
 * @method static Builder<static>|Purchase whereExchangeRate($value)
 * @method static Builder<static>|Purchase whereGrandTotal($value)
 * @method static Builder<static>|Purchase whereId($value)
 * @method static Builder<static>|Purchase whereItem($value)
 * @method static Builder<static>|Purchase whereNote($value)
 * @method static Builder<static>|Purchase whereOrderDiscount($value)
 * @method static Builder<static>|Purchase whereOrderTax($value)
 * @method static Builder<static>|Purchase whereOrderTaxRate($value)
 * @method static Builder<static>|Purchase wherePaidAmount($value)
 * @method static Builder<static>|Purchase wherePaymentStatus($value)
 * @method static Builder<static>|Purchase wherePurchaseType($value)
 * @method static Builder<static>|Purchase whereReferenceNo($value)
 * @method static Builder<static>|Purchase whereShippingCost($value)
 * @method static Builder<static>|Purchase whereStatus($value)
 * @method static Builder<static>|Purchase whereSupplierId($value)
 * @method static Builder<static>|Purchase whereTotalCost($value)
 * @method static Builder<static>|Purchase whereTotalDiscount($value)
 * @method static Builder<static>|Purchase whereTotalQty($value)
 * @method static Builder<static>|Purchase whereTotalTax($value)
 * @method static Builder<static>|Purchase whereUpdatedAt($value)
 * @method static Builder<static>|Purchase whereUserId($value)
 * @method static Builder<static>|Purchase whereWarehouseId($value)
 * @method static Builder<static>|Purchase withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Purchase withoutTrashed()
 * @mixin \Eloquent
 */
class Purchase extends Model implements AuditableContract
{
    use Auditable, HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'user_id',
        'warehouse_id',
        'supplier_id',
        'currency_id',
        'exchange_rate',
        'item',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_cost',
        'order_tax_rate',
        'order_tax',
        'order_discount',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'status',
        'payment_status',
        'document',
        'note',
        'purchase_type',
        'deleted_by',
    ];

    /**
     * Get the user who created this purchase.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier for this purchase.
     *
     * @return BelongsTo<Supplier, self>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse for this purchase.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the currency for this purchase.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the products in this purchase.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_purchases')
            ->withPivot('qty', 'tax', 'tax_rate', 'discount', 'total', 'product_batch_id', 'variant_id', 'net_unit_cost', 'net_unit_price')
            ->withTimestamps();
    }

    /**
     * Get the product purchases (pivot records).
     *
     * @return HasMany<ProductPurchase>
     */
    public function productPurchases(): HasMany
    {
        return $this->hasMany(ProductPurchase::class);
    }

    /**
     * Get the return purchases for this purchase.
     *
     * @return HasMany<ReturnPurchase>
     */
    public function returns(): HasMany
    {
        return $this->hasMany(ReturnPurchase::class);
    }

    /**
     * Get the payments for this purchase.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the installment plan for this purchase.
     *
     * @return MorphOne<InstallmentPlan>
     */
    public function installmentPlan(): MorphOne
    {
        return $this->morphOne(InstallmentPlan::class, 'reference');
    }

    /**
     * Get the user who deleted this purchase.
     *
     * @return BelongsTo<User, self>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Calculate the remaining amount to be paid.
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }

    /**
     * Check if the purchase is fully paid.
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->paid_amount >= $this->grand_total;
    }

    /**
     * Check if the purchase is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Scope a query to only include completed purchases.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope a query to only include pending purchases.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include paid purchases.
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include unpaid purchases.
     */
    public function scopeUnpaid(Builder $query): Builder
    {
        return $query->where('payment_status', '!=', 'paid');
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
            'warehouse_id' => 'integer',
            'supplier_id' => 'integer',
            'currency_id' => 'integer',
            'exchange_rate' => 'float',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_discount' => 'float',
            'total_tax' => 'float',
            'total_cost' => 'float',
            'order_tax_rate' => 'float',
            'order_tax' => 'float',
            'order_discount' => 'float',
            'shipping_cost' => 'float',
            'grand_total' => 'float',
            'paid_amount' => 'float',
            'deleted_by' => 'integer',
        ];
    }
}
