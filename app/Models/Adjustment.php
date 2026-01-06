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
 * Adjustment Model
 *
 * Represents a stock quantity adjustment for a warehouse.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $warehouse_id
 * @property string|null $document
 * @property float $total_qty
 * @property int $item
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, ProductAdjustment> $productAdjustments
 */
class Adjustment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'warehouse_id',
        'document',
        'total_qty',
        'item',
        'note',
    ];

    /**
     * Get the warehouse for this adjustment.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the product adjustments for this adjustment.
     *
     * @return HasMany<ProductAdjustment>
     */
    public function productAdjustments(): HasMany
    {
        return $this->hasMany(ProductAdjustment::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'warehouse_id' => 'integer',
            'total_qty' => 'float',
            'item' => 'integer',
        ];
    }
}

