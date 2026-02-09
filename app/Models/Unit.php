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
 * Unit Model
 *
 * Represents a measurement unit with support for base unit conversion.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $base_unit
 * @property string|null $operator
 * @property float|null $operation_value
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $status
 * @property-read Unit|null $baseUnitRelation
 * @property-read Collection<int, Unit> $subUnits
 * @property-read Collection<int, Product> $products
 *
 * @method static Builder|Unit active()
 */
class Unit extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'base_unit',
        'operator',
        'operation_value',
        'is_active',
    ];

    /**
     * Get the base unit for this unit.
     *
     * @return BelongsTo<Unit, self>
     */
    public function baseUnitRelation(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'base_unit');
    }

    /**
     * Get the sub-units that use this unit as base.
     *
     * @return HasMany<Unit>
     */
    public function subUnits(): HasMany
    {
        return $this->hasMany(Unit::class, 'base_unit');
    }

    /**
     * Get the products using this unit.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Convert a value from this unit to base unit.
     */
    public function convertToBase(float $value): float
    {
        if ($this->isBaseUnit()) {
            return $value;
        }

        return match ($this->operator) {
            '*' => $value * ($this->operation_value ?? 1),
            '/' => $value / ($this->operation_value ?? 1),
            '+' => $value + ($this->operation_value ?? 0),
            '-' => $value - ($this->operation_value ?? 0),
            default => $value,
        };
    }

    /**
     * Check if this is a base unit.
     */
    public function isBaseUnit(): bool
    {
        return $this->base_unit === null;
    }

    /**
     * Scope a query to only include active units.
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
            'base_unit' => 'integer',
            'operation_value' => 'float',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the human-readable status.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }
}
