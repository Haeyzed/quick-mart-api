<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Table Model
 *
 * Represents a restaurant table for POS system.
 *
 * @property int $id
 * @property string $name
 * @property int $number_of_person
 * @property string|null $description
 * @property int|null $floor_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Sale> $sales
 *
 * @method static Builder|Table active()
 */
class Table extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'number_of_person',
        'description',
        'floor_id',
        'is_active',
    ];

    /**
     * Check if the table is currently occupied.
     */
    public function isOccupied(): bool
    {
        return $this->sales()
            ->where('sale_status', '!=', 'completed')
            ->where('sale_status', '!=', 'cancelled')
            ->exists();
    }

    /**
     * Get the sales for this table.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Scope a query to only include active tables.
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
            'number_of_person' => 'integer',
            'floor_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
