<?php

declare(strict_types=1);

namespace App\Models;

use Nnjeim\World\Models\City as WorldCity;

/**
 * Class City
 *
 * Represents a city from World reference data. Extends Nnjeim\World City.
 *
 * @property int $id
 * @property int $country_id
 * @property int $state_id
 * @property string $name
 * @property string $country_code
 * @property string|null $state_code
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @method static \Illuminate\Database\Eloquent\Builder|City newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|City query()
 */
class City extends WorldCity
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'state_id',
        'name',
        'country_code',
        'state_code',
        'latitude',
        'longitude',
    ];
}
