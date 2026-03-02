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
 * Class Courier
 *
 * Represents a courier/delivery service provider. Handles the underlying data
 * structure, relationships, and specific query scopes for courier entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone_number
 * @property string|null $address
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Courier newModelQuery()
 * @method static Builder|Courier newQuery()
 * @method static Builder|Courier query()
 * @method static Builder|Courier active()
 * @method static Builder|Courier filter(array $filters)
 *
 * @property-read Collection<int, Delivery> $deliveries
 * @property-read int|null $deliveries_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Courier customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Courier last30Days(string $column = 'created_at')
 * @method static Builder<static>|Courier last7Days(string $column = 'created_at')
 * @method static Builder<static>|Courier lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Courier lastYear(string $column = 'created_at')
 * @method static Builder<static>|Courier monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Courier onlyTrashed()
 * @method static Builder<static>|Courier quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Courier today(string $column = 'created_at')
 * @method static Builder<static>|Courier whereAddress($value)
 * @method static Builder<static>|Courier whereCreatedAt($value)
 * @method static Builder<static>|Courier whereDeletedAt($value)
 * @method static Builder<static>|Courier whereId($value)
 * @method static Builder<static>|Courier whereIsActive($value)
 * @method static Builder<static>|Courier whereName($value)
 * @method static Builder<static>|Courier wherePhoneNumber($value)
 * @method static Builder<static>|Courier whereUpdatedAt($value)
 * @method static Builder<static>|Courier withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Courier withoutTrashed()
 * @method static Builder<static>|Courier yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Courier yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Courier extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone_number',
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
                        ->orWhere('phone_number', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the deliveries for this courier.
     *
     * @return HasMany<Delivery>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Scope a query to only include active couriers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
