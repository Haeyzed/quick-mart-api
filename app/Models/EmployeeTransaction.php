<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * EmployeeTransaction Model
 * 
 * Represents a financial transaction for an employee (advance, loan, etc.).
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $date
 * @property float $amount
 * @property string $type
 * @property string|null $description
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read User $creator
 * @method static Builder|EmployeeTransaction advance()
 * @method static Builder|EmployeeTransaction loan()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|EmployeeTransaction newModelQuery()
 * @method static Builder<static>|EmployeeTransaction newQuery()
 * @method static Builder<static>|EmployeeTransaction query()
 * @mixin \Eloquent
 */
class EmployeeTransaction extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'date',
        'amount',
        'type',
        'description',
        'created_by',
    ];

    /**
     * Get the employee for this transaction.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who created this transaction.
     *
     * @return BelongsTo<User, self>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include advance transactions.
     */
    public function scopeAdvance(Builder $query): Builder
    {
        return $query->where('type', 'advance');
    }

    /**
     * Scope a query to only include loan transactions.
     */
    public function scopeLoan(Builder $query): Builder
    {
        return $query->where('type', 'loan');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'employee_id' => 'integer',
            'date' => 'date',
            'amount' => 'float',
            'created_by' => 'integer',
        ];
    }
}
