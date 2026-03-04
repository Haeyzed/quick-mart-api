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
 * Class Challan
 * 
 * Represents a challan (delivery receipt) for courier services. Handles the underlying data
 * structure, relationships, and specific query scopes for challan entities.
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
 * @method static Builder|Challan newModelQuery()
 * @method static Builder|Challan newQuery()
 * @method static Builder|Challan query()
 * @method static Builder|Challan open()
 * @method static Builder|Challan closed()
 * @method static Builder|Challan filter(array $filters)
 * @property-read Courier|null $courier
 * @property-read User|null $createdBy
 * @property-read User|null $closedBy
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Challan customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Challan last30Days(string $column = 'created_at')
 * @method static Builder<static>|Challan last7Days(string $column = 'created_at')
 * @method static Builder<static>|Challan lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Challan lastYear(string $column = 'created_at')
 * @method static Builder<static>|Challan monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Challan quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Challan today(string $column = 'created_at')
 * @method static Builder<static>|Challan whereAmountList($value)
 * @method static Builder<static>|Challan whereCashList($value)
 * @method static Builder<static>|Challan whereChequeList($value)
 * @method static Builder<static>|Challan whereClosedById($value)
 * @method static Builder<static>|Challan whereClosingDate($value)
 * @method static Builder<static>|Challan whereCourierId($value)
 * @method static Builder<static>|Challan whereCreatedAt($value)
 * @method static Builder<static>|Challan whereCreatedById($value)
 * @method static Builder<static>|Challan whereDeliveryChargeList($value)
 * @method static Builder<static>|Challan whereId($value)
 * @method static Builder<static>|Challan whereOnlinePaymentList($value)
 * @method static Builder<static>|Challan wherePackingSlipList($value)
 * @method static Builder<static>|Challan whereReferenceNo($value)
 * @method static Builder<static>|Challan whereStatus($value)
 * @method static Builder<static>|Challan whereStatusList($value)
 * @method static Builder<static>|Challan whereUpdatedAt($value)
 * @method static Builder<static>|Challan yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Challan yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class Challan extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'courier_id' => 'integer',
        'closing_date' => 'datetime',
        'created_by_id' => 'integer',
        'closed_by_id' => 'integer',
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
                isset($filters['status']),
                fn(Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                !empty($filters['courier_id']),
                fn(Builder $q) => $q->where('courier_id', $filters['courier_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn(Builder $subQ) => $subQ
                        ->where('reference_no', 'like', $term)
                        ->orWhereHas('courier', function (Builder $courierQ) use ($term) {
                            $courierQ->where('name', 'like', $term);
                        })
                    );
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

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
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include closed challans.
     */
    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', 'closed');
    }
}
