<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * LeaveType Model
 *
 * Represents a type of leave (e.g., Annual, Sick, Casual).
 *
 * @property int $id
 * @property string $name
 * @property int $annual_quota
 * @property bool $encashable
 * @property int|null $carry_forward_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Leave> $leaves
 */
class LeaveType extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'leave_types';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'annual_quota',
        'encashable',
        'carry_forward_limit',
    ];

    /**
     * Get the leaves of this type.
     *
     * @return HasMany<Leave>
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'leave_types');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'annual_quota' => 'integer',
            'encashable' => 'boolean',
            'carry_forward_limit' => 'integer',
        ];
    }
}
