<?php

declare(strict_types=1);

namespace App\Models;

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
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Currency query()
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
}
