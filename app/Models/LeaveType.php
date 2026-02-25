<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class LeaveType
 * 
 * Represents a leave type within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for leave type entities.
 *
 * @property int $id
 * @property string $name
 * @property float $annual_quota
 * @property bool $encashable
 * @property float $carry_forward_limit
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|LeaveType newModelQuery()
 * @method static Builder|LeaveType newQuery()
 * @method static Builder|LeaveType query()
 * @method static Builder|LeaveType active()
 * @method static Builder|LeaveType filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Leave> $leaves
 * @property-read int|null $leaves_count
 * @method static Builder<static>|LeaveType customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|LeaveType last30Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveType last7Days(string $column = 'created_at')
 * @method static Builder<static>|LeaveType lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|LeaveType lastYear(string $column = 'created_at')
 * @method static Builder<static>|LeaveType monthToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveType onlyTrashed()
 * @method static Builder<static>|LeaveType quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveType today(string $column = 'created_at')
 * @method static Builder<static>|LeaveType whereAnnualQuota($value)
 * @method static Builder<static>|LeaveType whereCarryForwardLimit($value)
 * @method static Builder<static>|LeaveType whereCreatedAt($value)
 * @method static Builder<static>|LeaveType whereEncashable($value)
 * @method static Builder<static>|LeaveType whereId($value)
 * @method static Builder<static>|LeaveType whereName($value)
 * @method static Builder<static>|LeaveType whereUpdatedAt($value)
 * @method static Builder<static>|LeaveType withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|LeaveType withoutTrashed()
 * @method static Builder<static>|LeaveType yearToDate(string $column = 'created_at')
 * @method static Builder<static>|LeaveType yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class LeaveType extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'annual_quota',
        'encashable',
        'carry_forward_limit',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'annual_quota' => 'float',
        'encashable' => 'boolean',
        'carry_forward_limit' => 'float',
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
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active leave types.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the leaves associated with this type.
     *
     * Defines a one-to-many relationship linking this leave type to its leave requests.
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'leave_types');
    }
}
