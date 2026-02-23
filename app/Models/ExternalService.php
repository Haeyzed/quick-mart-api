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
 * ExternalService Model
 * 
 * Represents an external service integration configuration.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $details
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ExternalService active()
 * @property string|null $module_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|ExternalService newModelQuery()
 * @method static Builder<static>|ExternalService newQuery()
 * @method static Builder<static>|ExternalService query()
 * @method static Builder<static>|ExternalService whereActive($value)
 * @method static Builder<static>|ExternalService whereCreatedAt($value)
 * @method static Builder<static>|ExternalService whereDetails($value)
 * @method static Builder<static>|ExternalService whereId($value)
 * @method static Builder<static>|ExternalService whereModuleStatus($value)
 * @method static Builder<static>|ExternalService whereName($value)
 * @method static Builder<static>|ExternalService whereType($value)
 * @method static Builder<static>|ExternalService whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ExternalService extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'external_services';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'details',
        'active',
    ];

    /**
     * Scope a query to only include active external services.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }
}
