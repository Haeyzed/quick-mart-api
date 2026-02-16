<?php

declare(strict_types=1);

namespace App\Models;

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
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 */
class Language extends WorldLanguage
{
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
}
