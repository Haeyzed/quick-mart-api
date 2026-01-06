<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Expense Model
 *
 * Represents an expense transaction.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $expense_category_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property int|null $employee_id
 * @property string $type
 * @property float $amount
 * @property string|null $note
 * @property string|null $document
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read ExpenseCategory $expenseCategory
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @property-read Employee|null $employee
 */
class Expense extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'expense_category_id',
        'warehouse_id',
        'account_id',
        'user_id',
        'cash_register_id',
        'employee_id',
        'type',
        'amount',
        'note',
        'document',
        'created_at',
    ];

    /**
     * Get the expense category for this expense.
     *
     * @return BelongsTo<ExpenseCategory, self>
     */
    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class);
    }

    /**
     * Get the warehouse for this expense.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the account for this expense.
     *
     * @return BelongsTo<Account, self>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who created this expense.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cash register for this expense.
     *
     * @return BelongsTo<CashRegister, self>
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Get the employee for this expense.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'expense_category_id' => 'integer',
            'warehouse_id' => 'integer',
            'account_id' => 'integer',
            'user_id' => 'integer',
            'cash_register_id' => 'integer',
            'employee_id' => 'integer',
            'amount' => 'float',
            'created_at' => 'datetime',
        ];
    }
}

