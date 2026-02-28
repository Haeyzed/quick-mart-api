<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class SalaryStructureItem extends Model
{
    protected $fillable = [
        'salary_structure_id',
        'salary_component_id',
        'amount',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'percentage' => 'decimal:2',
        ];
    }

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
}
