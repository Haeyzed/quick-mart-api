<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class WorkLocation
 * 
 * Represents a physical or logical work location for employees.
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WorkLocation withoutTrashed()
 * @mixin \Eloquent
 */
class WorkLocation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the employees associated with this work location.
     *
     * @return HasMany<Employee, self>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'work_location_id');
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
