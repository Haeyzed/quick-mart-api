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

/**
 * Tax Model
 *
 * Represents a tax rate configuration.
 *
 * @property int $id
 * @property string $name
 * @property float $rate
 * @property bool $is_active
 * @property int|null $woocommerce_tax_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read string $status
 * @property-read Collection<int, Product> $products
 *
 * @method static Builder|Tax active()
 */
class Tax extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'rate',
        'is_active',
        'woocommerce_tax_id',
    ];

    /**
     * Get the products using this tax.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Calculate tax amount for a given value.
     *
     * @param float $value
     * @return float
     */
    public function calculateTax(float $value): float
    {
        return ($value * $this->rate) / 100;
    }

    /**
     * Scope a query to only include active taxes.
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
            'rate' => 'float',
            'is_active' => 'boolean',
            'woocommerce_tax_id' => 'integer',
        ];
    }

    /**
     * Get the human-readable status.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }
}

