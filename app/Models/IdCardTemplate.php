<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class IdCardTemplate
 *
 * Represents an ID card design template within the system.
 * Handles the underlying data structure and configuration casting.
 *
 * @property int $id
 * @property string $name
 * @property array $design_config
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class IdCardTemplate extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'design_config',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'design_config' => 'array',
        'is_active' => 'boolean',
    ];
}
