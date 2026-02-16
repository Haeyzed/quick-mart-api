<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Nnjeim\World\Models\Currency as WorldCurrencyBase;

/**
 * Class Currency
 *
 * Represents a currency from World reference data. Extends Nnjeim\World Currency.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $code
 * @property int $precision
 * @property string $symbol
 * @property string $symbol_native
 * @property bool $symbol_first
 * @property string $decimal_mark
 * @property string $thousands_separator
 *
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @method static Builder|Currency filter(array $filters)
 */
class Currency extends WorldCurrencyBase
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'name',
        'code',
        'precision',
        'symbol',
        'symbol_native',
        'symbol_first',
        'decimal_mark',
        'thousands_separator',
    ];

    /**
     * Scope a query to apply filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['search'] ?? null),
                fn ($query) => $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%'.$filters['search'].'%')
                        ->orWhere('code', 'like', '%'.$filters['search'].'%')
                        ->orWhere('symbol', 'like', '%'.$filters['search'].'%');
                })
            )
            ->when(
                ! empty($filters['country_id'] ?? null),
                fn (Builder $q) => $q->where('country_id', $filters['country_id'])
            );
    }
}
