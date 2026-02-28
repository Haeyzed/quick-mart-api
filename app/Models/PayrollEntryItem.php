<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PayrollEntryItem
 *
 * Line item for a salary component amount within a payroll entry.
 *
 * @property int $id
 * @property int $payroll_entry_id
 * @property int $salary_component_id
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class PayrollEntryItem extends Model
{
    protected $fillable = [
        'payroll_entry_id',
        'salary_component_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<PayrollEntry, self>
     */
    public function payrollEntry(): BelongsTo
    {
        return $this->belongsTo(PayrollEntry::class);
    }

    /**
     * @return BelongsTo<SalaryComponent, self>
     */
    public function salaryComponent(): BelongsTo
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
