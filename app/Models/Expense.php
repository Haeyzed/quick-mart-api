<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Expense
 *
 * Represents an expense transaction. Handles the underlying data
 * structure, relationships, and specific query scopes for expense entities.
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
 * @property int|null $boutique_id
 *
 * @method static Builder|Expense newModelQuery()
 * @method static Builder|Expense newQuery()
 * @method static Builder|Expense query()
 * @method static Builder|Expense filter(array $filters)
 *
 * @property-read \App\Models\ExpenseCategory $expenseCategory
 * @property-read \App\Models\Warehouse $warehouse
 * @property-read \App\Models\Account|null $account
 * @property-read \App\Models\User $user
 * @property-read \App\Models\CashRegister|null $cashRegister
 * @property-read \App\Models\Employee|null $employee
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Expense customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Expense last30Days(string $column = 'created_at')
 * @method static Builder<static>|Expense last7Days(string $column = 'created_at')
 * @method static Builder<static>|Expense lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Expense lastYear(string $column = 'created_at')
 * @method static Builder<static>|Expense monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Expense quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Expense today(string $column = 'created_at')
 * @method static Builder<static>|Expense whereAccountId($value)
 * @method static Builder<static>|Expense whereAmount($value)
 * @method static Builder<static>|Expense whereBoutiqueId($value)
 * @method static Builder<static>|Expense whereCashRegisterId($value)
 * @method static Builder<static>|Expense whereCreatedAt($value)
 * @method static Builder<static>|Expense whereDocument($value)
 * @method static Builder<static>|Expense whereEmployeeId($value)
 * @method static Builder<static>|Expense whereExpenseCategoryId($value)
 * @method static Builder<static>|Expense whereId($value)
 * @method static Builder<static>|Expense whereNote($value)
 * @method static Builder<static>|Expense whereReferenceNo($value)
 * @method static Builder<static>|Expense whereType($value)
 * @method static Builder<static>|Expense whereUpdatedAt($value)
 * @method static Builder<static>|Expense whereUserId($value)
 * @method static Builder<static>|Expense whereWarehouseId($value)
 * @method static Builder<static>|Expense yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Expense yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Expense extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expense_category_id' => 'integer',
        'warehouse_id' => 'integer',
        'account_id' => 'integer',
        'user_id' => 'integer',
        'cash_register_id' => 'integer',
        'employee_id' => 'integer',
        'amount' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['warehouse_id']),
                fn (Builder $q) => $q->where('warehouse_id', (int) $filters['warehouse_id'])
            )
            ->when(
                ! empty($filters['expense_category_id']),
                fn (Builder $q) => $q->where('expense_category_id', (int) $filters['expense_category_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('reference_no', 'like', $term)
                        ->orWhere('note', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
                'created_at'
            );
    }

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
}
