<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Supplier Model
 *
 * Represents a supplier/vendor in the system.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string|null $company_name
 * @property string|null $vat_number
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $address
 * @property string|null $city
 * @property string|null $state
 * @property string|null $postal_code
 * @property string|null $country
 * @property float $opening_balance
 * @property int|null $pay_term_no
 * @property string|null $pay_term_period
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, ReturnPurchase> $returnPurchases
 * @property-read Collection<int, Product> $products
 *
 * @method static Builder|Supplier active()
 */
class Supplier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'company_name',
        'vat_number',
        'email',
        'phone_number',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'opening_balance',
        'pay_term_no',
        'pay_term_period',
        'is_active',
    ];

    /**
     * Get the return purchases for this supplier.
     *
     * @return HasMany<ReturnPurchase>
     */
    public function returnPurchases(): HasMany
    {
        return $this->hasMany(ReturnPurchase::class);
    }

    /**
     * Get the products from this supplier.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Calculate the total due amount for this supplier.
     *
     * @return float
     */
    public function getTotalDue(): float
    {
        $totalPurchases = $this->purchases()->where('payment_status', '!=', 'paid')->sum('grand_total');
        $totalPaid = $this->purchases()->sum('paid_amount');

        return max(0, $totalPurchases - $totalPaid);
    }

    /**
     * Get the purchases from this supplier.
     *
     * @return HasMany<Purchase>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Scope a query to only include active suppliers.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_balance' => 'float',
            'pay_term_no' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

