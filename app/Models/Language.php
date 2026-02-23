<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\Language as WorldLanguage;

/**
 * Class Language
 * 
 * Represents a language from World reference data. Extends Nnjeim\World Language.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $name_native
 * @property string $dir
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 * @method static Builder<static>|Language customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Language filter(array $filters)
 * @method static Builder<static>|Language last30Days(string $column = 'created_at')
 * @method static Builder<static>|Language last7Days(string $column = 'created_at')
 * @method static Builder<static>|Language lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Language lastYear(string $column = 'created_at')
 * @method static Builder<static>|Language monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Language quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Language today(string $column = 'created_at')
 * @method static Builder<static>|Language whereCode($value)
 * @method static Builder<static>|Language whereDir($value)
 * @method static Builder<static>|Language whereId($value)
 * @method static Builder<static>|Language whereName($value)
 * @method static Builder<static>|Language whereNameNative($value)
 * @method static Builder<static>|Language yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Language yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Language extends WorldLanguage
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'name_native',
        'dir',
    ];

    /**
     * Scope a query to apply filters.
     *
     * @param array<string, mixed> $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                !empty($filters['search']),
                fn($q) => $q->where('name', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('code', 'like', '%' . $filters['search'] . '%')
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}
