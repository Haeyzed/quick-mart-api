<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Variant Model
 * 
 * Represents a product variant option (e.g., Size, Color).
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @method static Builder|Variant byName(string $name)
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $products_count
 * @method static Builder<static>|Variant newModelQuery()
 * @method static Builder<static>|Variant newQuery()
 * @method static Builder<static>|Variant onlyTrashed()
 * @method static Builder<static>|Variant query()
 * @method static Builder<static>|Variant whereCreatedAt($value)
 * @method static Builder<static>|Variant whereDeletedAt($value)
 * @method static Builder<static>|Variant whereId($value)
 * @method static Builder<static>|Variant whereName($value)
 * @method static Builder<static>|Variant whereUpdatedAt($value)
 * @method static Builder<static>|Variant withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Variant withoutTrashed()
 * @mixin \Eloquent
 */
class Variant extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the products that have this variant.
     *
     * @return BelongsToMany<Product>
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variants')
            ->withPivot('id', 'item_code', 'additional_cost', 'additional_price', 'qty')
            ->withTimestamps();
    }

    /**
     * Scope a query to filter by variant name.
     */
    public function scopeByName(Builder $query, string $name): Builder
    {
        return $query->where('name', $name);
    }
}
