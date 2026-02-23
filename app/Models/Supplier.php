<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Supplier Model
 * 
 * Represents a supplier/vendor in the system.
 * Follows the same structure as Customer: country_id, state_id, city_id, scopeFilter, active scope.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string|null $image_url
 * @property string|null $company_name
 * @property string|null $vat_number
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $wa_number
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property float $opening_balance
 * @property int|null $pay_term_no
 * @property string|null $pay_term_period
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, ReturnPurchase> $returnPurchases
 * @property-read Collection<int, Product> $products
 * @method static Builder|Supplier active()
 * @method static Builder|Supplier filter(array $filters)
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $products_count
 * @property-read int|null $purchases_count
 * @property-read int|null $return_purchases_count
 * @method static Builder<static>|Supplier newModelQuery()
 * @method static Builder<static>|Supplier newQuery()
 * @method static Builder<static>|Supplier onlyTrashed()
 * @method static Builder<static>|Supplier query()
 * @method static Builder<static>|Supplier whereAddress($value)
 * @method static Builder<static>|Supplier whereCityId($value)
 * @method static Builder<static>|Supplier whereCompanyName($value)
 * @method static Builder<static>|Supplier whereCountryId($value)
 * @method static Builder<static>|Supplier whereCreatedAt($value)
 * @method static Builder<static>|Supplier whereDeletedAt($value)
 * @method static Builder<static>|Supplier whereEmail($value)
 * @method static Builder<static>|Supplier whereId($value)
 * @method static Builder<static>|Supplier whereImage($value)
 * @method static Builder<static>|Supplier whereImageUrl($value)
 * @method static Builder<static>|Supplier whereIsActive($value)
 * @method static Builder<static>|Supplier whereName($value)
 * @method static Builder<static>|Supplier whereOpeningBalance($value)
 * @method static Builder<static>|Supplier wherePayTermNo($value)
 * @method static Builder<static>|Supplier wherePayTermPeriod($value)
 * @method static Builder<static>|Supplier wherePhoneNumber($value)
 * @method static Builder<static>|Supplier wherePostalCode($value)
 * @method static Builder<static>|Supplier whereStateId($value)
 * @method static Builder<static>|Supplier whereUpdatedAt($value)
 * @method static Builder<static>|Supplier whereVatNumber($value)
 * @method static Builder<static>|Supplier whereWaNumber($value)
 * @method static Builder<static>|Supplier withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Supplier withoutTrashed()
 * @mixin \Eloquent
 */
class Supplier extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'image_url',
        'company_name',
        'vat_number',
        'email',
        'phone_number',
        'wa_number',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'postal_code',
        'opening_balance',
        'pay_term_no',
        'pay_term_period',
        'is_active',
    ];

    /**
     * Get the country.
     *
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state.
     *
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

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
     * Get the purchases from this supplier.
     *
     * @return HasMany<Purchase>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Calculate the total due amount for this supplier.
     */
    public function getTotalDue(): float
    {
        $totalPurchases = $this->purchases()->where('payment_status', '!=', 'paid')->sum('grand_total');
        $totalPaid = $this->purchases()->sum('paid_amount');

        return max(0, $totalPurchases - $totalPaid);
    }

    /**
     * Scope a query to only include active suppliers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to apply filters (status, search). Same pattern as Customer::scopeFilter.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                function (Builder $q) use ($filters) {
                    if ($filters['status'] === 'active') {
                        $q->active();
                    } elseif ($filters['status'] === 'inactive') {
                        $q->where('is_active', false);
                    }
                }
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = '%'.$filters['search'].'%';
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                    );
                }
            );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'opening_balance' => 'float',
            'pay_term_no' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
