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
 * Designation Model
 * 
 * Represents a job designation/position in the organization.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Employee> $employees
 * @method static Builder|Designation active()
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $employees_count
 * @method static Builder<static>|Designation newModelQuery()
 * @method static Builder<static>|Designation newQuery()
 * @method static Builder<static>|Designation onlyTrashed()
 * @method static Builder<static>|Designation query()
 * @method static Builder<static>|Designation whereCreatedAt($value)
 * @method static Builder<static>|Designation whereDeletedAt($value)
 * @method static Builder<static>|Designation whereId($value)
 * @method static Builder<static>|Designation whereIsActive($value)
 * @method static Builder<static>|Designation whereName($value)
 * @method static Builder<static>|Designation whereUpdatedAt($value)
 * @method static Builder<static>|Designation withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Designation withoutTrashed()
 * @mixin \Eloquent
 */
class Designation extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_active',
    ];

    /**
     * Get the employees with this designation.
     *
     * @return HasMany<Employee>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scope a query to only include active designations.
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
