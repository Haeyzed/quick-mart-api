<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * MobileToken Model
 * 
 * Represents a mobile device token for API authentication.
 *
 * @property int $id
 * @property string $name
 * @property string|null $ip
 * @property string|null $location
 * @property string $token
 * @property bool $is_active
 * @property Carbon|null $last_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|MobileToken active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|MobileToken newModelQuery()
 * @method static Builder<static>|MobileToken newQuery()
 * @method static Builder<static>|MobileToken query()
 * @method static Builder<static>|MobileToken whereCreatedAt($value)
 * @method static Builder<static>|MobileToken whereId($value)
 * @method static Builder<static>|MobileToken whereIp($value)
 * @method static Builder<static>|MobileToken whereIsActive($value)
 * @method static Builder<static>|MobileToken whereLastActive($value)
 * @method static Builder<static>|MobileToken whereLocation($value)
 * @method static Builder<static>|MobileToken whereName($value)
 * @method static Builder<static>|MobileToken whereToken($value)
 * @method static Builder<static>|MobileToken whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class MobileToken extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'ip',
        'location',
        'token',
        'is_active',
        'last_active',
    ];

    /**
     * Scope a query to only include active tokens.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_active' => 'datetime',
        ];
    }
}
