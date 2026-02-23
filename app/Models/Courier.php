<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Courier Model
 * 
 * Represents a courier/delivery service provider.
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone_number
 * @property string|null $address
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Delivery> $deliveries
 * @method static Builder|Courier active()
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $deliveries_count
 * @method static Builder<static>|Courier newModelQuery()
 * @method static Builder<static>|Courier newQuery()
 * @method static Builder<static>|Courier onlyTrashed()
 * @method static Builder<static>|Courier query()
 * @method static Builder<static>|Courier whereAddress($value)
 * @method static Builder<static>|Courier whereCreatedAt($value)
 * @method static Builder<static>|Courier whereDeletedAt($value)
 * @method static Builder<static>|Courier whereId($value)
 * @method static Builder<static>|Courier whereIsActive($value)
 * @method static Builder<static>|Courier whereName($value)
 * @method static Builder<static>|Courier wherePhoneNumber($value)
 * @method static Builder<static>|Courier whereUpdatedAt($value)
 * @method static Builder<static>|Courier withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Courier withoutTrashed()
 * @mixin \Eloquent
 */
class Courier extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'is_active',
    ];

    /**
     * Get the deliveries for this courier.
     *
     * @return HasMany<Delivery>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }

    /**
     * Scope a query to only include active couriers.
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
        ];
    }
}
