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
 * Class MoneyTransfer
 * 
 * Represents a money transfer between two accounts. Handles the underlying data
 * structure, relationships, and specific query scopes for money transfer entities.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $from_account_id
 * @property int $to_account_id
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|MoneyTransfer newModelQuery()
 * @method static Builder|MoneyTransfer newQuery()
 * @method static Builder|MoneyTransfer query()
 * @method static Builder|MoneyTransfer filter(array $filters)
 * @property-read Account $fromAccount
 * @property-read Account $toAccount
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|MoneyTransfer customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer last30Days(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer last7Days(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer lastYear(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer monthToDate(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer today(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer whereAmount($value)
 * @method static Builder<static>|MoneyTransfer whereCreatedAt($value)
 * @method static Builder<static>|MoneyTransfer whereFromAccountId($value)
 * @method static Builder<static>|MoneyTransfer whereId($value)
 * @method static Builder<static>|MoneyTransfer whereReferenceNo($value)
 * @method static Builder<static>|MoneyTransfer whereToAccountId($value)
 * @method static Builder<static>|MoneyTransfer whereUpdatedAt($value)
 * @method static Builder<static>|MoneyTransfer yearToDate(string $column = 'created_at')
 * @method static Builder<static>|MoneyTransfer yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class MoneyTransfer extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'from_account_id',
        'to_account_id',
        'amount',
        'created_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'from_account_id' => 'integer',
        'to_account_id' => 'integer',
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
                !empty($filters['from_account_id']),
                fn(Builder $q) => $q->where('from_account_id', (int)$filters['from_account_id'])
            )
            ->when(
                !empty($filters['to_account_id']),
                fn(Builder $q) => $q->where('to_account_id', (int)$filters['to_account_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('reference_no', 'like', $term);
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
                'created_at'
            );
    }

    /**
     * Get the source account for this transfer.
     *
     * @return BelongsTo<Account, self>
     */
    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    /**
     * Get the destination account for this transfer.
     *
     * @return BelongsTo<Account, self>
     */
    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }
}
