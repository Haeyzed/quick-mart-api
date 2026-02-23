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
 * Quotation Model
 * 
 * Represents a quotation/quote for a customer or supplier.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $biller_id
 * @property int|null $supplier_id
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float|null $order_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property string $quotation_status
 * @property string|null $document
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Biller $biller
 * @property-read Supplier|null $supplier
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, ProductQuotation> $productQuotations
 * @method static Builder|Quotation pending()
 * @method static Builder|Quotation accepted()
 * @method static Builder|Quotation rejected()
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_quotations_count
 * @method static Builder<static>|Quotation newModelQuery()
 * @method static Builder<static>|Quotation newQuery()
 * @method static Builder<static>|Quotation query()
 * @method static Builder<static>|Quotation whereBillerId($value)
 * @method static Builder<static>|Quotation whereCreatedAt($value)
 * @method static Builder<static>|Quotation whereCustomerId($value)
 * @method static Builder<static>|Quotation whereDocument($value)
 * @method static Builder<static>|Quotation whereGrandTotal($value)
 * @method static Builder<static>|Quotation whereId($value)
 * @method static Builder<static>|Quotation whereItem($value)
 * @method static Builder<static>|Quotation whereNote($value)
 * @method static Builder<static>|Quotation whereOrderDiscount($value)
 * @method static Builder<static>|Quotation whereOrderTax($value)
 * @method static Builder<static>|Quotation whereOrderTaxRate($value)
 * @method static Builder<static>|Quotation whereQuotationStatus($value)
 * @method static Builder<static>|Quotation whereReferenceNo($value)
 * @method static Builder<static>|Quotation whereShippingCost($value)
 * @method static Builder<static>|Quotation whereSupplierId($value)
 * @method static Builder<static>|Quotation whereTotalDiscount($value)
 * @method static Builder<static>|Quotation whereTotalPrice($value)
 * @method static Builder<static>|Quotation whereTotalQty($value)
 * @method static Builder<static>|Quotation whereTotalTax($value)
 * @method static Builder<static>|Quotation whereUpdatedAt($value)
 * @method static Builder<static>|Quotation whereUserId($value)
 * @method static Builder<static>|Quotation whereWarehouseId($value)
 * @mixin \Eloquent
 */
class Quotation extends Model implements AuditableContract
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
        'biller_id',
        'supplier_id',
        'customer_id',
        'warehouse_id',
        'item',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_price',
        'order_tax_rate',
        'order_tax',
        'order_discount',
        'shipping_cost',
        'grand_total',
        'quotation_status',
        'document',
        'note',
    ];

    /**
     * Get the user who created this quotation.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the biller for this quotation.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    /**
     * Get the supplier for this quotation.
     *
     * @return BelongsTo<Supplier, self>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the customer for this quotation.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the warehouse for this quotation.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product quotations for this quotation.
     *
     * @return HasMany<ProductQuotation>
     */
    public function productQuotations(): HasMany
    {
        return $this->hasMany(ProductQuotation::class);
    }

    /**
     * Scope a query to only include pending quotations.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending($query)
    {
        return $query->where('quotation_status', 'pending');
    }

    /**
     * Scope a query to only include accepted quotations.
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('quotation_status', 'accepted');
    }

    /**
     * Scope a query to only include rejected quotations.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('quotation_status', 'rejected');
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
            'biller_id' => 'integer',
            'supplier_id' => 'integer',
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_discount' => 'float',
            'total_tax' => 'float',
            'total_price' => 'float',
            'order_tax_rate' => 'float',
            'order_tax' => 'float',
            'order_discount' => 'float',
            'shipping_cost' => 'float',
            'grand_total' => 'float',
        ];
    }
}
