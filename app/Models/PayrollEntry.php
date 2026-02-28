<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class PayrollEntry
 *
 * Single employee's payroll record within a payroll run.
 *
 * @property int $id
 * @property int $payroll_run_id
 * @property int $employee_id
 * @property float $gross_salary
 * @property float $total_deductions
 * @property float $net_salary
 * @property string $status
 */
class PayrollEntry extends Model
{
    protected $fillable = [
        'payroll_run_id',
        'employee_id',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'gross_salary' => 'decimal:2',
            'total_deductions' => 'decimal:2',
            'net_salary' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PayrollEntryItem::class, 'payroll_entry_id');
    }
}
