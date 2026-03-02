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
 * Class HrmSetting
 *
 * Represents HRM (Human Resource Management) system settings. Handles the underlying data
 * structure, relationships, and specific query scopes for HRM setting entities.
 *
 * @property int $id
 * @property string $checkin
 * @property string $checkout
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|HrmSetting newModelQuery()
 * @method static Builder|HrmSetting newQuery()
 * @method static Builder|HrmSetting query()
 * @method static Builder|HrmSetting filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|HrmSetting customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|HrmSetting last30Days(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting last7Days(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting lastYear(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting monthToDate(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting today(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting whereCheckin($value)
 * @method static Builder<static>|HrmSetting whereCheckout($value)
 * @method static Builder<static>|HrmSetting whereCreatedAt($value)
 * @method static Builder<static>|HrmSetting whereId($value)
 * @method static Builder<static>|HrmSetting whereUpdatedAt($value)
 * @method static Builder<static>|HrmSetting yearToDate(string $column = 'created_at')
 * @method static Builder<static>|HrmSetting yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class HrmSetting extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'checkin',
        'checkout',
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
        return $query->customRange(
            ! empty($filters['start_date']) ? $filters['start_date'] : null,
            ! empty($filters['end_date']) ? $filters['end_date'] : null,
        );
    }
}
