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
 * Class Income
 * 
 * Represents an income transaction.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $income_category_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property float $amount
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read IncomeCategory $incomeCategory
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @method static Builder|Income filter(array $filters)
 * @property int|null $boutique_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Income customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Income last30Days(string $column = 'created_at')
 * @method static Builder<static>|Income last7Days(string $column = 'created_at')
 * @method static Builder<static>|Income lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Income lastYear(string $column = 'created_at')
 * @method static Builder<static>|Income monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Income newModelQuery()
 * @method static Builder<static>|Income newQuery()
 * @method static Builder<static>|Income quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Income query()
 * @method static Builder<static>|Income today(string $column = 'created_at')
 * @method static Builder<static>|Income whereAccountId($value)
 * @method static Builder<static>|Income whereAmount($value)
 * @method static Builder<static>|Income whereBoutiqueId($value)
 * @method static Builder<static>|Income whereCashRegisterId($value)
 * @method static Builder<static>|Income whereCreatedAt($value)
 * @method static Builder<static>|Income whereId($value)
 * @method static Builder<static>|Income whereIncomeCategoryId($value)
 * @method static Builder<static>|Income whereNote($value)
 * @method static Builder<static>|Income whereReferenceNo($value)
 * @method static Builder<static>|Income whereUpdatedAt($value)
 * @method static Builder<static>|Income whereUserId($value)
 * @method static Builder<static>|Income whereWarehouseId($value)
 * @method static Builder<static>|Income yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Income yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Income extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'income_category_id',
        'warehouse_id',
        'account_id',
        'user_id',
        'cash_register_id',
        'amount',
        'note',
        'created_at',
    ];

    /**
     * Get the income category for this income.
     *
     * @return BelongsTo<IncomeCategory, self>
     */
    public function incomeCategory(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class);
    }

    /**
     * Get the warehouse for this income.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the account for this income.
     *
     * @return BelongsTo<Account, self>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the user who created this income.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cash register for this income.
     *
     * @return BelongsTo<CashRegister, self>
     */
    public function cashRegister(): BelongsTo
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['warehouse_id'] ?? null),
                fn (Builder $q) => $q->where('warehouse_id', (int) $filters['warehouse_id'])
            )
            ->when(
                ! empty($filters['user_id'] ?? null),
                fn (Builder $q) => $q->where('user_id', (int) $filters['user_id'])
            )
            ->when(
                ! empty($filters['search'] ?? null),
                fn (Builder $q) => $q->where('reference_no', 'like', '%'.$filters['search'].'%')
            )
            ->customRange(
                $filters['start_date'] ?? $filters['starting_date'] ?? null,
                $filters['end_date'] ?? $filters['ending_date'] ?? null,
                'created_at'
            );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'income_category_id' => 'integer',
            'warehouse_id' => 'integer',
            'account_id' => 'integer',
            'user_id' => 'integer',
            'cash_register_id' => 'integer',
            'amount' => 'float',
            'created_at' => 'datetime',
        ];
    }
}
