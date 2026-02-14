<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;

/**
 * Custom Permission model with module support for grouping.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Permission extends SpatiePermission
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'guard_name',
        'module',
    ];
}
