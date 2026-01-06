<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Payroll Model
 *
 * Represents a payroll payment for an employee.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $employee_id
 * @property int|null $account_id
 * @property int $user_id
 * @property float $amount
 * @property string $paying_method
 * @property string|null $note
 * @property string $status
 * @property array|null $amount_array
 * @property string|null $month
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Employee $employee
 * @property-read Account|null $account
 * @property-read User $user
 *
 * @method static Builder|Payroll paid()
 * @method static Builder|Payroll pending()
 */
class Payroll extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'employee_id',
        'account_id',
        'user_id',
        'amount',
        'paying_method',
        'note',
        'status',
        'amount_array',
        'month',
        'created_at',
    ];

    /**
     * Get the employee for this payroll.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the account for this payroll.
     *
     * @return BelongsTo<Account, self>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who processed this payroll.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include paid payrolls.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope a query to only include pending payrolls.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
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
            'account_id' => 'integer',
            'user_id' => 'integer',
            'amount' => 'float',
            'amount_array' => 'array',
            'created_at' => 'datetime',
        ];
    }
}

