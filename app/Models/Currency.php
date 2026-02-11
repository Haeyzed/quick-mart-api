<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Currency Model
 *
 * Represents a currency with exchange rate.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $symbol
 * @property float $exchange_rate
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Payment> $payments
 *
 * @method static Builder|Currency active()
 */
class Currency extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
        'is_active',
    ];

    /**
     * Get the sales using this currency.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the purchases using this currency.
     *
     * @return HasMany<Purchase>
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    /**
     * Get the payments using this currency.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Convert an amount from this currency to base currency.
     */
    public function convertToBase(float $amount): float
    {
        return $amount * $this->exchange_rate;
    }

    /**
     * Convert an amount from base currency to this currency.
     */
    public function convertFromBase(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Scope a query to only include active currencies.
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
            'exchange_rate' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
