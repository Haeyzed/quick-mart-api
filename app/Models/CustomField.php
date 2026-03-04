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
 * Class CustomField
 * 
 * Represents a custom field that can be added to various entities. Handles the underlying data
 * structure, relationships, and specific query scopes for custom field entities.
 *
 * @property int $id
 * @property string $belongs_to
 * @property string $name
 * @property string $type
 * @property string|null $default_value
 * @property string|null $option_value
 * @property string|null $grid_value
 * @property bool $is_table
 * @property bool $is_invoice
 * @property bool $is_required
 * @property bool $is_admin
 * @property bool $is_disable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CustomField newModelQuery()
 * @method static Builder|CustomField newQuery()
 * @method static Builder|CustomField query()
 * @method static Builder|CustomField filter(array $filters)
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|CustomField customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|CustomField last30Days(string $column = 'created_at')
 * @method static Builder<static>|CustomField last7Days(string $column = 'created_at')
 * @method static Builder<static>|CustomField lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|CustomField lastYear(string $column = 'created_at')
 * @method static Builder<static>|CustomField monthToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomField quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomField today(string $column = 'created_at')
 * @method static Builder<static>|CustomField whereBelongsTo($value)
 * @method static Builder<static>|CustomField whereCreatedAt($value)
 * @method static Builder<static>|CustomField whereDefaultValue($value)
 * @method static Builder<static>|CustomField whereGridValue($value)
 * @method static Builder<static>|CustomField whereId($value)
 * @method static Builder<static>|CustomField whereIsAdmin($value)
 * @method static Builder<static>|CustomField whereIsDisable($value)
 * @method static Builder<static>|CustomField whereIsInvoice($value)
 * @method static Builder<static>|CustomField whereIsRequired($value)
 * @method static Builder<static>|CustomField whereIsTable($value)
 * @method static Builder<static>|CustomField whereName($value)
 * @method static Builder<static>|CustomField whereOptionValue($value)
 * @method static Builder<static>|CustomField whereType($value)
 * @method static Builder<static>|CustomField whereUpdatedAt($value)
 * @method static Builder<static>|CustomField yearToDate(string $column = 'created_at')
 * @method static Builder<static>|CustomField yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class CustomField extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'belongs_to',
        'name',
        'type',
        'default_value',
        'option_value',
        'grid_value',
        'is_table',
        'is_invoice',
        'is_required',
        'is_admin',
        'is_disable',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_table' => 'boolean',
        'is_invoice' => 'boolean',
        'is_required' => 'boolean',
        'is_admin' => 'boolean',
        'is_disable' => 'boolean',
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
                !empty($filters['belongs_to']),
                fn(Builder $q) => $q->where('belongs_to', $filters['belongs_to'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}
