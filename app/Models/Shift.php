<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Shift Model
 * 
 * Represents a work shift with time schedules.
 *
 * @property int $id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property int|null $grace_in
 * @property int|null $grace_out
 * @property float|null $total_hours
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Employee> $employees
 * @method static Builder|Shift active()
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $employees_count
 * @method static Builder<static>|Shift newModelQuery()
 * @method static Builder<static>|Shift newQuery()
 * @method static Builder<static>|Shift query()
 * @method static Builder<static>|Shift whereCreatedAt($value)
 * @method static Builder<static>|Shift whereEndTime($value)
 * @method static Builder<static>|Shift whereGraceIn($value)
 * @method static Builder<static>|Shift whereGraceOut($value)
 * @method static Builder<static>|Shift whereId($value)
 * @method static Builder<static>|Shift whereIsActive($value)
 * @method static Builder<static>|Shift whereName($value)
 * @method static Builder<static>|Shift whereStartTime($value)
 * @method static Builder<static>|Shift whereTotalHours($value)
 * @method static Builder<static>|Shift whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Shift extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shifts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'grace_in',
        'grace_out',
        'total_hours',
        'is_active',
    ];

    /**
     * Get the employees assigned to this shift.
     *
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope a query to only include active shifts.
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
            'grace_in' => 'integer',
            'grace_out' => 'integer',
            'total_hours' => 'float',
            'is_active' => 'boolean',
        ];
    }
}
