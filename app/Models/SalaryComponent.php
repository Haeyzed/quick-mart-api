<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class SalaryComponent
 * 
 * Represents an earning or deduction component (e.g. Basic, Housing, Tax).
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property bool $is_taxable
 * @property string $calculation_type
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalaryStructureItem> $structureItems
 * @property-read int|null $structure_items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SalaryStructure> $structures
 * @property-read int|null $structures_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent active()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent deductions()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent earnings()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereCalculationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereIsTaxable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryComponent withoutTrashed()
 * @mixin \Eloquent
 */
class SalaryComponent extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPE_EARNING = 'earning';

    public const TYPE_DEDUCTION = 'deduction';

    protected $fillable = [
        'name',
        'type',
        'is_taxable',
        'calculation_type',
        'is_active',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEarnings($query)
    {
        return $query->where('type', self::TYPE_EARNING);
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', self::TYPE_DEDUCTION);
    }

    /**
     * Get the structure items that reference this component.
     *
     * @return HasMany<SalaryStructureItem, self>
     */
    public function structureItems(): HasMany
    {
        return $this->hasMany(SalaryStructureItem::class, 'salary_component_id');
    }

    /**
     * Get the salary structures that include this component.
     *
     * @return BelongsToMany<SalaryStructure, self>
     */
    public function structures(): BelongsToMany
    {
        return $this->belongsToMany(SalaryStructure::class, 'salary_structure_items', 'salary_component_id', 'salary_structure_id')
            ->withPivot(['amount', 'percentage'])
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'is_taxable' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
