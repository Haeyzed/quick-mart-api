<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Income Model
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
 */
class Income extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
