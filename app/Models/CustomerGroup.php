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
 * Class CustomerGroup
 * 
 * Represents a customer group within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for customer group entities.
 *
 * @property int $id
 * @property string $name
 * @property float $percentage
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Customer> $customers
 * @method static Builder|CustomerGroup newModelQuery()
 * @method static Builder|CustomerGroup newQuery()
 * @method static Builder|CustomerGroup query()
 * @method static Builder|CustomerGroup active()
 * @method static Builder|CustomerGroup filter(array $filters)
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $customers_count
 * @method static Builder<static>|CustomerGroup customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup last30Days(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup last7Days(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup lastYear(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup monthToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup onlyTrashed()
 * @method static Builder<static>|CustomerGroup quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup today(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup whereCreatedAt($value)
 * @method static Builder<static>|CustomerGroup whereDeletedAt($value)
 * @method static Builder<static>|CustomerGroup whereId($value)
 * @method static Builder<static>|CustomerGroup whereIsActive($value)
 * @method static Builder<static>|CustomerGroup whereName($value)
 * @method static Builder<static>|CustomerGroup wherePercentage($value)
 * @method static Builder<static>|CustomerGroup whereUpdatedAt($value)
 * @method static Builder<static>|CustomerGroup withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|CustomerGroup withoutTrashed()
 * @method static Builder<static>|CustomerGroup yearToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomerGroup yesterday(string $column = 'current_at')
 * @mixin \Eloquent
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
     * Defines a one-to-many relationship linking this customer group to its customers.
     *
     * @return HasMany<Customer>
     */
    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Scope a query to only include active customer groups.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to apply dynamic filters.
     *
     * Applies filters for active status, search terms (name), and date ranges via the FilterableByDates trait.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
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
     * Get the attributes that should be cast to native types.
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
