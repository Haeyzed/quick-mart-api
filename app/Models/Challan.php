<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Challan Model
 *
 * Represents a challan (delivery receipt) for courier services.
 *
 * @property int $id
 * @property string $reference_no
 * @property int|null $courier_id
 * @property string $status
 * @property string|null $packing_slip_list
 * @property string|null $amount_list
 * @property string|null $cash_list
 * @property string|null $cheque_list
 * @property string|null $online_payment_list
 * @property string|null $delivery_charge_list
 * @property string|null $status_list
 * @property Carbon|null $closing_date
 * @property int|null $created_by_id
 * @property int|null $closed_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Courier|null $courier
 * @property-read User|null $createdBy
 * @property-read User|null $closedBy
 *
 * @method static Builder|Challan open()
 * @method static Builder|Challan closed()
 */
class Challan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_no',
        'courier_id',
        'status',
        'packing_slip_list',
        'amount_list',
        'cash_list',
        'cheque_list',
        'online_payment_list',
        'delivery_charge_list',
        'status_list',
        'closing_date',
        'created_by_id',
        'closed_by_id',
        'created_at',
    ];

    /**
     * Get the courier for this challan.
     *
     * @return BelongsTo<Courier, self>
     */
    public function courier(): BelongsTo
    {
        return $this->belongsTo(Courier::class);
    }

    /**
     * Get the user who created this challan.
     *
     * @return BelongsTo<User, self>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /**
     * Get the user who closed this challan.
     *
     * @return BelongsTo<User, self>
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    /**
     * Scope a query to only include open challans.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed challans.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'courier_id' => 'integer',
            'closing_date' => 'datetime',
            'created_by_id' => 'integer',
            'closed_by_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}

