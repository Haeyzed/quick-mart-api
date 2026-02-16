<?php

declare(strict_types=1);

namespace App\Models;

use Nnjeim\World\Models\State as WorldState;

/**
 * Class State
 *
 * Represents a state/region from World reference data. Extends Nnjeim\World State.
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property int|null $country_id
 * @property string|null $country_code
 * @property string|null $state_code
 * @property string|null $type
 * @property string|null $latitude
 * @property string|null $longitude
 *
 * @method static \Illuminate\Database\Eloquent\Builder|State newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|State query()
 */
class State extends WorldState
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'country_id',
        'country_code',
        'state_code',
        'type',
        'latitude',
        'longitude',
    ];
}
