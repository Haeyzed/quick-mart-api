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
 * Class Deposit
 *
 * Represents a customer deposit transaction. Handles the underlying data
 * structure, relationships, and specific query scopes for deposit entities.
 *
 * @property int $id
 * @property float $amount
 * @property int $customer_id
 * @property int $user_id
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|Deposit newModelQuery()
 * @method static Builder|Deposit newQuery()
 * @method static Builder|Deposit query()
 * @method static Builder|Deposit filter(array $filters)
 *
 * @property-read \App\Models\Customer $customer
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Deposit customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Deposit last30Days(string $column = 'created_at')
 * @method static Builder<static>|Deposit last7Days(string $column = 'created_at')
 * @method static Builder<static>|Deposit lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Deposit lastYear(string $column = 'created_at')
 * @method static Builder<static>|Deposit monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Deposit quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Deposit today(string $column = 'created_at')
 * @method static Builder<static>|Deposit whereAmount($value)
 * @method static Builder<static>|Deposit whereCreatedAt($value)
 * @method static Builder<static>|Deposit whereCustomerId($value)
 * @method static Builder<static>|Deposit whereId($value)
 * @method static Builder<static>|Deposit whereNote($value)
 * @method static Builder<static>|Deposit whereUpdatedAt($value)
 * @method static Builder<static>|Deposit whereUserId($value)
 * @method static Builder<static>|Deposit yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Deposit yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Deposit extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'customer_id',
        'user_id',
        'note',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'float',
        'customer_id' => 'integer',
        'user_id' => 'integer',
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
                ! empty($filters['customer_id']),
                fn (Builder $q) => $q->where('customer_id', $filters['customer_id'])
            )
            ->when(
                ! empty($filters['user_id']),
                fn (Builder $q) => $q->where('user_id', $filters['user_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('note', 'like', $term)
                        ->orWhereHas('customer', function (Builder $subQ) use ($term) {
                            $subQ->where('name', 'like', $term);
                        });
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the customer for this deposit.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user who created this deposit.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
