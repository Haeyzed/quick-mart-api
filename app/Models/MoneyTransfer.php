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
 * MoneyTransfer Model
 *
 * Represents a money transfer between two accounts.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $from_account_id
 * @property int $to_account_id
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account $fromAccount
 * @property-read Account $toAccount
 */
class MoneyTransfer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'from_account_id' => 'integer',
            'to_account_id' => 'integer',
            'amount' => 'float',
            'created_at' => 'datetime',
        ];
    }
}
