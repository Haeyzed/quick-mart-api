<?php

declare(strict_types=1);

namespace App\Models;

use Nnjeim\World\Models\Timezone as WorldTimezone;

/**
 * Class Timezone
 *
 * Represents a timezone from World reference data. Extends Nnjeim\World Timezone.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Timezone query()
 */
class Timezone extends WorldTimezone
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'country_id',
        'name',
    ];
}
