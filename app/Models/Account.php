<?php

declare(strict_types=1);

namespace App\Models;

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
 * Account Model
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
 * @property-read Account|null $parent
 * @property-read Collection<int, Account> $children
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, MoneyTransfer> $fromTransfers
 * @property-read Collection<int, MoneyTransfer> $toTransfers
 *
 * @method static Builder|Account active()
 * @method static Builder|Account default()
 */
class Account extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'initial_balance' => 'float',
            'total_balance' => 'float',
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'parent_account_id' => 'integer',
            'is_payment' => 'boolean',
        ];
    }
}
