<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class PayrollEntryItem
 * 
 * Line item for a salary component amount within a payroll entry.
 *
 * @property int $id
 * @property int $payroll_entry_id
 * @property int $salary_component_id
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\PayrollEntry $payrollEntry
 * @property-read \App\Models\SalaryComponent $salaryComponent
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem wherePayrollEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem whereSalaryComponentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PayrollEntryItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class PayrollEntryItem extends Model
{
    protected $fillable = [
        'payroll_entry_id',
        'salary_component_id',
        'amount',
    ];

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

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }
}
