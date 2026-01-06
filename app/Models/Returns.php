<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Returns Model (Sale Return)
 *
 * Represents a return transaction for a sale.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $sale_id
 * @property int|null $cash_register_id
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $biller_id
 * @property int|null $account_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $return_note
 * @property string|null $staff_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User $user
 * @property-read Sale $sale
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Biller $biller
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Collection<int, ProductReturn> $productReturns
 */
class Returns extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'returns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'user_id',
        'sale_id',
        'cash_register_id',
        'customer_id',
        'warehouse_id',
        'biller_id',
        'account_id',
        'currency_id',
        'exchange_rate',
        'item',
        'total_qty',
        'total_discount',
        'total_tax',
        'total_price',
        'order_tax_rate',
        'order_tax',
        'grand_total',
        'document',
        'return_note',
        'staff_note',
    ];

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
     * Get the sale that this return is for.
     *
     * @return BelongsTo<Sale, self>
     */
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the customer for this return.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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
     * Get the biller for this return.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
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
     * Get the product returns for this return.
     *
     * @return HasMany<ProductReturn>
     */
    public function productReturns(): HasMany
    {
        return $this->hasMany(ProductReturn::class, 'return_id');
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
            'sale_id' => 'integer',
            'cash_register_id' => 'integer',
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'biller_id' => 'integer',
            'account_id' => 'integer',
            'currency_id' => 'integer',
            'exchange_rate' => 'float',
            'item' => 'integer',
            'total_qty' => 'float',
            'total_discount' => 'float',
            'total_tax' => 'float',
            'total_price' => 'float',
            'order_tax_rate' => 'float',
            'order_tax' => 'float',
            'grand_total' => 'float',
        ];
    }
}

