<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EmployeeShiftAssignment
 *
 * Assignment of an employee to a shift with effective date range (for rotating/flex shifts).
 *
 * @property int $id
 * @property int $employee_id
 * @property int $shift_id
 * @property \Illuminate\Support\Carbon $effective_from
 * @property \Illuminate\Support\Carbon|null $effective_to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class EmployeeShiftAssignment extends Model
{
    protected $fillable = [
        'employee_id',
        'shift_id',
        'effective_from',
        'effective_to',
    ];

    protected function casts(): array
    {
        return [
            'effective_from' => 'date',
            'effective_to' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Shift, self>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
