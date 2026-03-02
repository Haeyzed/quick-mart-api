<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Account
 *
 * Represents a financial account in the accounting system.
 *
 * @property int $id
 * @property string $account_no
 * @property string $name
 * @property float $initial_balance
 * @property float $total_balance
 * @property string|null $note
 * @property bool $is_default
 * @property bool $is_active
 * @property string|null $code
 * @property string|null $type
 * @property int|null $parent_account_id
 * @property bool|null $is_payment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $deleted_at
 *
 * @method static Builder|Account newModelQuery()
 * @method static Builder|Account newQuery()
 * @method static Builder|Account query()
 * @method static Builder|Account active()
 * @method static Builder|Account default()
 * @method static Builder|Account filter(array $filters)
 *
 * @property-read Account|null $parent
 * @property-read Collection<int, Account> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, MoneyTransfer> $fromTransfers
 * @property-read int|null $from_transfers_count
 * @property-read Collection<int, MoneyTransfer> $toTransfers
 * @property-read int|null $to_transfers_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Account customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Account last30Days(string $column = 'created_at')
 * @method static Builder<static>|Account last7Days(string $column = 'created_at')
 * @method static Builder<static>|Account lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Account lastYear(string $column = 'created_at')
 * @method static Builder<static>|Account monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Account quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Account today(string $column = 'created_at')
 * @method static Builder<static>|Account whereAccountNo($value)
 * @method static Builder<static>|Account whereCode($value)
 * @method static Builder<static>|Account whereCreatedAt($value)
 * @method static Builder<static>|Account whereDeletedAt($value)
 * @method static Builder<static>|Account whereId($value)
 * @method static Builder<static>|Account whereInitialBalance($value)
 * @method static Builder<static>|Account whereIsActive($value)
 * @method static Builder<static>|Account whereIsDefault($value)
 * @method static Builder<static>|Account whereIsPayment($value)
 * @method static Builder<static>|Account whereName($value)
 * @method static Builder<static>|Account whereNote($value)
 * @method static Builder<static>|Account whereParentAccountId($value)
 * @method static Builder<static>|Account whereTotalBalance($value)
 * @method static Builder<static>|Account whereType($value)
 * @method static Builder<static>|Account whereUpdatedAt($value)
 * @method static Builder<static>|Account yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Account yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Account extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'account_no',
        'name',
        'initial_balance',
        'total_balance',
        'note',
        'is_default',
        'is_active',
        'code',
        'type',
        'parent_account_id',
        'is_payment',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'initial_balance' => 'float',
        'total_balance' => 'float',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'parent_account_id' => 'integer',
        'is_payment' => 'boolean',
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
                isset($filters['is_active']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('account_no', 'like', $term)
                        ->orWhere('code', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include default account.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the parent account.
     *
     * @return BelongsTo<Account, self>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_account_id');
    }

    /**
     * Get the child accounts.
     *
     * @return HasMany<Account>
     */
    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_account_id');
    }

    /**
     * Get the payments for this account.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the money transfers from this account.
     *
     * @return HasMany<MoneyTransfer>
     */
    public function fromTransfers(): HasMany
    {
        return $this->hasMany(MoneyTransfer::class, 'from_account_id');
    }

    /**
     * Get the money transfers to this account.
     *
     * @return HasMany<MoneyTransfer>
     */
    public function toTransfers(): HasMany
    {
        return $this->hasMany(MoneyTransfer::class, 'to_account_id');
    }
}
