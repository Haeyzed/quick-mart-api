<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class MobileToken
 *
 * Represents a mobile device token for API authentication. Handles the underlying data
 * structure, relationships, and specific query scopes for mobile token entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $ip
 * @property string|null $location
 * @property string $token
 * @property bool $is_active
 * @property Carbon|null $last_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|MobileToken newModelQuery()
 * @method static Builder|MobileToken newQuery()
 * @method static Builder|MobileToken query()
 * @method static Builder|MobileToken active()
 * @method static Builder|MobileToken filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|MobileToken customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|MobileToken last30Days(string $column = 'created_at')
 * @method static Builder<static>|MobileToken last7Days(string $column = 'created_at')
 * @method static Builder<static>|MobileToken lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|MobileToken lastYear(string $column = 'created_at')
 * @method static Builder<static>|MobileToken monthToDate(string $column = 'created_at')
 * @method static Builder<static>|MobileToken quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|MobileToken today(string $column = 'created_at')
 * @method static Builder<static>|MobileToken whereCreatedAt($value)
 * @method static Builder<static>|MobileToken whereId($value)
 * @method static Builder<static>|MobileToken whereIp($value)
 * @method static Builder<static>|MobileToken whereIsActive($value)
 * @method static Builder<static>|MobileToken whereLastActive($value)
 * @method static Builder<static>|MobileToken whereLocation($value)
 * @method static Builder<static>|MobileToken whereName($value)
 * @method static Builder<static>|MobileToken whereToken($value)
 * @method static Builder<static>|MobileToken whereUpdatedAt($value)
 * @method static Builder<static>|MobileToken yearToDate(string $column = 'created_at')
 * @method static Builder<static>|MobileToken yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class MobileToken extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ip',
        'location',
        'token',
        'is_active',
        'last_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'last_active' => 'datetime',
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
                    $q->where('name', 'like', $term)
                        ->orWhere('ip', 'like', $term)
                        ->orWhere('location', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active tokens.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
