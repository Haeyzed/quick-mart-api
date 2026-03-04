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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class CashRegister
 * 
 * Represents a cash register session for a user in a warehouse. Handles the underlying data
 * structure, relationships, and specific query scopes for cash register entities.
 *
 * @property int $id
 * @property float $cash_in_hand
 * @property int $user_id
 * @property int $warehouse_id
 * @property string $status
 * @property float|null $closing_balance
 * @property float|null $actual_cash
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|CashRegister newModelQuery()
 * @method static Builder|CashRegister newQuery()
 * @method static Builder|CashRegister query()
 * @method static Builder|CashRegister open()
 * @method static Builder|CashRegister closed()
 * @method static Builder|CashRegister filter(array $filters)
 * @property-read User $user
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, Sale> $sales
 * @property-read int|null $sales_count
 * @property-read Collection<int, Payment> $payments
 * @property-read int|null $payments_count
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|CashRegister customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|CashRegister last30Days(string $column = 'created_at')
 * @method static Builder<static>|CashRegister last7Days(string $column = 'created_at')
 * @method static Builder<static>|CashRegister lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|CashRegister lastYear(string $column = 'created_at')
 * @method static Builder<static>|CashRegister monthToDate(string $column = 'created_at')
 * @method static Builder<static>|CashRegister quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|CashRegister today(string $column = 'created_at')
 * @method static Builder<static>|CashRegister whereActualCash($value)
 * @method static Builder<static>|CashRegister whereCashInHand($value)
 * @method static Builder<static>|CashRegister whereClosingBalance($value)
 * @method static Builder<static>|CashRegister whereCreatedAt($value)
 * @method static Builder<static>|CashRegister whereId($value)
 * @method static Builder<static>|CashRegister whereStatus($value)
 * @method static Builder<static>|CashRegister whereUpdatedAt($value)
 * @method static Builder<static>|CashRegister whereUserId($value)
 * @method static Builder<static>|CashRegister whereWarehouseId($value)
 * @method static Builder<static>|CashRegister yearToDate(string $column = 'created_at')
 * @method static Builder<static>|CashRegister yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class CashRegister extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cash_in_hand',
        'user_id',
        'warehouse_id',
        'status',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cash_in_hand' => 'float',
        'user_id' => 'integer',
        'warehouse_id' => 'integer',
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
                isset($filters['status']),
                fn(Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                !empty($filters['warehouse_id']),
                fn(Builder $q) => $q->where('warehouse_id', $filters['warehouse_id'])
            )
            ->when(
                !empty($filters['user_id']),
                fn(Builder $q) => $q->where('user_id', $filters['user_id'])
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the user for this cash register.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the warehouse for this cash register.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the sales for this cash register.
     *
     * @return HasMany<Sale>
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get the payments for this cash register.
     *
     * @return HasMany<Payment>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Check if the cash register is open.
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if the cash register is closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Scope a query to only include open cash registers.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed cash registers.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }
}
