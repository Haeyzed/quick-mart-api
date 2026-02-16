<?php

declare(strict_types=1);

namespace App\Models;

use Nnjeim\World\Models\Country as WorldCountry;

/**
 * Class Country
 *
 * Represents a country from World reference data. Extends Nnjeim\World Country.
 *
 * @property int $id
 * @property string $iso2
 * @property string $name
 * @property int $status
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $native
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Country newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Country query()
 */
class Country extends WorldCountry
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'iso2',
        'name',
        'status',
        'phone_code',
        'iso3',
        'region',
        'subregion',
        'native',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
    ];
}
