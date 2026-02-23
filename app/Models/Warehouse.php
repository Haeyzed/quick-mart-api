<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Warehouse
 * 
 * Represents a warehouse within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for warehouse entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse active()
 * @method static Builder|Warehouse filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Printer> $printers
 * @property-read int|null $printers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductWarehouse> $productWarehouses
 * @property-read int|null $product_warehouses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Purchase> $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Warehouse customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Warehouse last30Days(string $column = 'created_at')
 * @method static Builder<static>|Warehouse last7Days(string $column = 'created_at')
 * @method static Builder<static>|Warehouse lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Warehouse lastYear(string $column = 'created_at')
 * @method static Builder<static>|Warehouse monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Warehouse onlyTrashed()
 * @method static Builder<static>|Warehouse quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Warehouse today(string $column = 'created_at')
 * @method static Builder<static>|Warehouse whereAddress($value)
 * @method static Builder<static>|Warehouse whereCreatedAt($value)
 * @method static Builder<static>|Warehouse whereDeletedAt($value)
 * @method static Builder<static>|Warehouse whereEmail($value)
 * @method static Builder<static>|Warehouse whereId($value)
 * @method static Builder<static>|Warehouse whereIsActive($value)
 * @method static Builder<static>|Warehouse whereName($value)
 * @method static Builder<static>|Warehouse wherePhone($value)
 * @method static Builder<static>|Warehouse whereUpdatedAt($value)
 * @method static Builder<static>|Warehouse withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Warehouse withoutTrashed()
 * @method static Builder<static>|Warehouse yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Warehouse yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Warehouse extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to apply dynamic filters.
     * * Applies filters for active status, search terms (checking name, email, phone),
     * and date ranges via the FilterableByDates trait.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active warehouses.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the product-warehouse pivot records.
     * * Defines a one-to-many relationship linking this warehouse to its product stock records.
     */
    public function productWarehouses(): HasMany
    {
        return $this->hasMany(ProductWarehouse::class);
    }

    /**
     * Get the products associated with this warehouse (via pivot with qty).
     * * Defines a many-to-many relationship through the product_warehouse table.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_warehouse')
            ->withPivot('qty')
            ->withTimestamps();
    }

    /**
     * Get the sales for this warehouse.
     * * Defines a one-to-many relationship linking this warehouse to its sales.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the purchases for this warehouse.
     * * Defines a one-to-many relationship linking this warehouse to its purchases.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the users associated with this warehouse.
     * * Defines a one-to-many relationship linking this warehouse to its users.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the printers associated with this warehouse.
     * * Defines a one-to-many relationship linking this warehouse to its printers.
     */
    public function printers(): HasMany
    {
        return $this->hasMany(Printer::class);
    }
}
