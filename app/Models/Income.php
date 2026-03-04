<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class Income
 * 
 * Represents an income transaction. Handles the underlying data
 * structure, relationships, and specific query scopes for income entities.
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
 * @property int|null $boutique_id
 * @method static Builder|Income newModelQuery()
 * @method static Builder|Income newQuery()
 * @method static Builder|Income query()
 * @method static Builder|Income filter(array $filters)
 * @property-read IncomeCategory $incomeCategory
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Income customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Income last30Days(string $column = 'created_at')
 * @method static Builder<static>|Income last7Days(string $column = 'created_at')
 * @method static Builder<static>|Income lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Income lastYear(string $column = 'created_at')
 * @method static Builder<static>|Income monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Income quarterToDate(string $column = 'created_at')
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
 * @mixin Eloquent
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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'income_category_id' => 'integer',
        'warehouse_id' => 'integer',
        'account_id' => 'integer',
        'user_id' => 'integer',
        'cash_register_id' => 'integer',
        'amount' => 'float',
        'created_at' => 'datetime',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @param array<string, mixed> $filters An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                !empty($filters['warehouse_id']),
                fn(Builder $q) => $q->where('warehouse_id', (int)$filters['warehouse_id'])
            )
            ->when(
                !empty($filters['user_id']),
                fn(Builder $q) => $q->where('user_id', (int)$filters['user_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('reference_no', 'like', $term);
                }
            )
            ->customRange(
                $filters['start_date'] ?? $filters['starting_date'] ?? null,
                $filters['end_date'] ?? $filters['ending_date'] ?? null,
            );
    }

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
}
