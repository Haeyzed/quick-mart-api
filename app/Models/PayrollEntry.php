<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class PayrollEntry
 *
 * Represents a single employee's payroll record within a payroll run. Handles the
 * underlying data structure, relationships, and salary figures for one employee per run.
 *
 * @property int $id
 * @property int $payroll_run_id
 * @property int $employee_id
 * @property float $gross_salary
 * @property float $total_deductions
 * @property float $net_salary
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|PayrollEntry newModelQuery()
 * @method static Builder|PayrollEntry newQuery()
 * @method static Builder|PayrollEntry query()
 *
 * @property-read \App\Models\Employee $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PayrollEntryItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\PayrollRun $payrollRun
 *
 * @method static Builder<static>|PayrollEntry whereCreatedAt($value)
 * @method static Builder<static>|PayrollEntry whereEmployeeId($value)
 * @method static Builder<static>|PayrollEntry whereGrossSalary($value)
 * @method static Builder<static>|PayrollEntry whereId($value)
 * @method static Builder<static>|PayrollEntry whereNetSalary($value)
 * @method static Builder<static>|PayrollEntry wherePayrollRunId($value)
 * @method static Builder<static>|PayrollEntry whereStatus($value)
 * @method static Builder<static>|PayrollEntry whereTotalDeductions($value)
 * @method static Builder<static>|PayrollEntry whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PayrollEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    /**
     * Get the payroll run this entry belongs to.
     */
    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    /**
     * Get the employee associated with this payroll entry.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the line items (salary components) for this entry.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayrollEntryItem::class, 'payroll_entry_id');
    }
}
