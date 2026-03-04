<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class SalaryStructureItem
 * 
 * Pivot model linking a salary structure to a component with amount/percentage.
 *
 * @property int $id
 * @property int $salary_structure_id
 * @property int $salary_component_id
 * @property float $amount
 * @property float|null $percentage
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\SalaryComponent $salaryComponent
 * @property-read \App\Models\SalaryStructure $salaryStructure
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereSalaryComponentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereSalaryStructureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalaryStructureItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class SalaryStructureItem extends Model
{
    protected $fillable = [
        'salary_structure_id',
        'salary_component_id',
        'amount',
        'percentage',
    ];

    /**
     * @return BelongsTo<SalaryStructure, self>
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * @return BelongsTo<SalaryComponent, self>
     */
    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }
}
