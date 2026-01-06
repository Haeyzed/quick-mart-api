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
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Sale Model
 *
 * Represents a sale transaction in the system.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property int|null $table_id
 * @property int|null $queue
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $biller_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property string|null $order_discount_type
 * @property float|null $order_discount_value
 * @property float|null $order_discount
 * @property int|null $coupon_id
 * @property float|null $coupon_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property string $sale_status
 * @property string $payment_status
 * @property string|null $billing_name
 * @property string|null $billing_phone
 * @property string|null $billing_email
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_country
 * @property string|null $billing_zip
 * @property string|null $shipping_name
 * @property string|null $shipping_phone
 * @property string|null $shipping_email
 * @property string|null $shipping_address
 * @property string|null $shipping_city
 * @property string|null $shipping_state
 * @property string|null $shipping_country
 * @property string|null $shipping_zip
 * @property string $sale_type
 * @property int|null $service_id
 * @property int|null $waiter_id
 * @property float $paid_amount
 * @property string|null $document
 * @property string|null $sale_note
 * @property string|null $staff_note
 * @property int|null $woocommerce_order_id
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @property-read User $user
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Biller $biller
 * @property-read Table|null $table
 * @property-read CashRegister|null $cashRegister
 * @property-read Currency|null $currency
 * @property-read Coupon|null $coupon
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, ProductSale> $productSales
 * @property-read Collection<int, Payment> $payments
 * @property-read Delivery|null $delivery
 * @property-read Returns|null $return
 * @property-read InstallmentPlan|null $installmentPlan
 * @property-read User|null $deleter
 *
 * @method static Builder|Sale completed()
 * @method static Builder|Sale pending()
 * @method static Builder|Sale paid()
 * @method static Builder|Sale unpaid()
 */
class Sale extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'user_id',
        'cash_register_id',
        'table_id',
        'queue',
        'customer_id',
        'warehouse_id',
        'biller_id',
        'item',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_price',
        'order_tax_rate',
        'order_tax',
        'order_discount_type',
        'order_discount_value',
        'order_discount',
        'coupon_id',
        'coupon_discount',
        'shipping_cost',
        'grand_total',
        'currency_id',
        'exchange_rate',
        'sale_status',
        'payment_status',
        'billing_name',
        'billing_phone',
        'billing_email',
        'billing_address',
        'billing_city',
        'billing_state',
        'billing_country',
        'billing_zip',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'shipping_city',
        'shipping_state',
        'shipping_country',
        'shipping_zip',
        'sale_type',
        'service_id',
        'waiter_id',
        'paid_amount',
        'document',
        'sale_note',
        'staff_note',
        'woocommerce_order_id',
        'deleted_by',
    ];

    /**
     * Get the user who created this sale.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the customer for this sale.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the warehouse for this sale.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the biller for this sale.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    /**
     * Get the table for this sale.
     *
     * @return BelongsTo<Table, self>
     */
    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    /**
     * Get the cash register for this sale.
     *
     * @return BelongsTo<CashRegister, self>
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Get the currency for this sale.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the coupon used for this sale.
     *
     * @return BelongsTo<Coupon, self>
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the products in this sale.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_sales')
            ->withPivot('qty', 'product_batch_id', 'return_qty', 'net_unit_price', 'tax', 'discount', 'tax_rate', 'total', 'is_delivered', 'variant_id', 'imei_number')
            ->withTimestamps();
    }

    /**
     * Get the product sales (pivot records).
     *
     * @return HasMany<ProductSale>
     */
    public function productSales(): HasMany
    {
        return $this->hasMany(ProductSale::class);
    }

    /**
     * Get the payments for this sale.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the delivery for this sale.
     *
     * @return HasOne<Delivery>
     */
    public function delivery(): HasOne
    {
        return $this->hasOne(Delivery::class);
    }

    /**
     * Get the return for this sale.
     *
     * @return HasOne<Returns>
     */
    public function return(): HasOne
    {
        return $this->hasOne(Returns::class);
    }

    /**
     * Get the installment plan for this sale.
     *
     * @return MorphOne<InstallmentPlan>
     */
    public function installmentPlan(): MorphOne
    {
        return $this->morphOne(InstallmentPlan::class, 'reference');
    }

    /**
     * Get the user who deleted this sale.
     *
     * @return BelongsTo<User, self>
     */
    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Calculate the remaining amount to be paid.
     *
     * @return float
     */
    public function getRemainingAmount(): float
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }

    /**
     * Check if the sale is fully paid.
     *
     * @return bool
     */
    public function isFullyPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->paid_amount >= $this->grand_total;
    }

    /**
     * Check if the sale is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->sale_status === 'completed';
    }

    /**
     * Scope a query to only include completed sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('sale_status', 'completed');
    }

    /**
     * Scope a query to only include pending sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('sale_status', 'pending');
    }

    /**
     * Scope a query to only include paid sales.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope a query to only include unpaid sales.
     *
     * @param Builder $query
     * @return Builder
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
            'cash_register_id' => 'integer',
            'table_id' => 'integer',
            'queue' => 'integer',
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'biller_id' => 'integer',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_discount' => 'float',
            'total_tax' => 'float',
            'total_price' => 'float',
            'order_tax_rate' => 'float',
            'order_tax' => 'float',
            'order_discount_value' => 'float',
            'order_discount' => 'float',
            'coupon_id' => 'integer',
            'coupon_discount' => 'float',
            'shipping_cost' => 'float',
            'grand_total' => 'float',
            'currency_id' => 'integer',
            'exchange_rate' => 'float',
            'service_id' => 'integer',
            'waiter_id' => 'integer',
            'paid_amount' => 'float',
            'woocommerce_order_id' => 'integer',
            'deleted_by' => 'integer',
        ];
    }
}

