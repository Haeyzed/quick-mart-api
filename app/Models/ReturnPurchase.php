<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * ReturnPurchase Model
 * 
 * Represents a return transaction for a purchase.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $purchase_id
 * @property int $user_id
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $return_note
 * @property string|null $staff_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Purchase $purchase
 * @property-read User $user
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Collection<int, PurchaseProductReturn> $purchaseProductReturns
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $purchase_product_returns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereReturnNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereStaffNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereWarehouseId($value)
 * @mixin \Eloquent
 */
class ReturnPurchase extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'return_purchases';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'purchase_id',
        'user_id',
        'supplier_id',
        'warehouse_id',
        'account_id',
        'currency_id',
        'exchange_rate',
        'item',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_cost',
        'order_tax_rate',
        'order_tax',
        'grand_total',
        'document',
        'return_note',
        'staff_note',
    ];

    /**
     * Get the purchase that this return is for.
     *
     * @return BelongsTo<Purchase, self>
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Get the user who processed this return.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the supplier for this return.
     *
     * @return BelongsTo<Supplier, self>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * Get the warehouse for this return.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the account for this return.
     *
     * @return BelongsTo<Account, self>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the currency for this return.
     *
     * @return BelongsTo<Currency, self>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Get the purchase product returns for this return.
     *
     * @return HasMany<PurchaseProductReturn>
     */
    public function purchaseProductReturns(): HasMany
    {
        return $this->hasMany(PurchaseProductReturn::class, 'return_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'purchase_id' => 'integer',
            'user_id' => 'integer',
            'supplier_id' => 'integer',
            'warehouse_id' => 'integer',
            'account_id' => 'integer',
            'currency_id' => 'integer',
            'exchange_rate' => 'float',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_discount' => 'float',
            'total_tax' => 'float',
            'total_cost' => 'float',
            'order_tax_rate' => 'float',
            'order_tax' => 'float',
            'grand_total' => 'float',
        ];
    }
}
