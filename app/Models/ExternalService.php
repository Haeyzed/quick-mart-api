<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class ExternalService
 * 
 * Represents an external service integration configuration. Handles the underlying data
 * structure, relationships, and specific query scopes for external service entities.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $details
 * @property bool $active
 * @property string|null $module_status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ExternalService newModelQuery()
 * @method static Builder|ExternalService newQuery()
 * @method static Builder|ExternalService query()
 * @method static Builder|ExternalService active()
 * @method static Builder|ExternalService filter(array $filters)
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|ExternalService customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|ExternalService last30Days(string $column = 'created_at')
 * @method static Builder<static>|ExternalService last7Days(string $column = 'created_at')
 * @method static Builder<static>|ExternalService lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|ExternalService lastYear(string $column = 'created_at')
 * @method static Builder<static>|ExternalService monthToDate(string $column = 'created_at')
 * @method static Builder<static>|ExternalService quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|ExternalService today(string $column = 'created_at')
 * @method static Builder<static>|ExternalService whereActive($value)
 * @method static Builder<static>|ExternalService whereCreatedAt($value)
 * @method static Builder<static>|ExternalService whereDetails($value)
 * @method static Builder<static>|ExternalService whereId($value)
 * @method static Builder<static>|ExternalService whereModuleStatus($value)
 * @method static Builder<static>|ExternalService whereName($value)
 * @method static Builder<static>|ExternalService whereType($value)
 * @method static Builder<static>|ExternalService whereUpdatedAt($value)
 * @method static Builder<static>|ExternalService yearToDate(string $column = 'created_at')
 * @method static Builder<static>|ExternalService yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class ExternalService extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'external_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'details',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @param array<string, mixed> $filters An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn(Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('type', 'like', $term)
                    );
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active external services.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
