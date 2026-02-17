<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
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
 * CustomerGroup Model
 *
 * Represents a customer group with discount percentage.
 *
 * @property int $id
 * @property string $name
 * @property float $percentage
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Customer> $customers
 *
 * @method static Builder|CustomerGroup active()
 * @method static Builder|CustomerGroup filter(array $filters)
 */
class CustomerGroup extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'percentage',
        'is_active',
    ];

    /**
     * Get the customers in this group.
     *
     * @return HasMany<Customer>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scope a query to only include active customer groups.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search'] ?? null),
                function (Builder $q) use ($filters) {
                    $term = '%'.$filters['search'].'%';
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date'] ?? null) ? $filters['start_date'] : null,
                ! empty($filters['end_date'] ?? null) ? $filters['end_date'] : null,
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
            'percentage' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
