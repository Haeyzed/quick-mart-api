<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class SalaryStructure
 *
 * Represents a salary structure template (e.g. monthly pay) with linked components.
 *
 * @property int $id
 * @property string $name
 * @property string $pay_frequency
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */
class SalaryStructure extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'pay_frequency',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the structure items (component + amount/percentage) for this structure.
     *
     * @return HasMany<SalaryStructureItem, self>
     */
    public function structureItems(): HasMany
    {
        return $this->hasMany(SalaryStructureItem::class, 'salary_structure_id');
    }

    /**
     * Get the salary components linked through structure items.
     *
     * @return BelongsToMany<SalaryComponent, self>
     */
    public function components(): BelongsToMany
    {
        return $this->belongsToMany(SalaryComponent::class, 'salary_structure_items', 'salary_structure_id', 'salary_component_id')
            ->withPivot(['amount', 'percentage'])
            ->withTimestamps();
    }

    /**
     * Get the employees assigned to this salary structure.
     *
     * @return HasMany<Employee, self>
     */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'salary_structure_id');
    }
}
