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
 * Class GiftCardRecharge
 * 
 * Represents a recharge transaction for a gift card. Handles the underlying data
 * structure, relationships, and specific query scopes for gift card recharge entities.
 *
 * @property int $id
 * @property int $gift_card_id
 * @property float $amount
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|GiftCardRecharge newModelQuery()
 * @method static Builder|GiftCardRecharge newQuery()
 * @method static Builder|GiftCardRecharge query()
 * @method static Builder|GiftCardRecharge filter(array $filters)
 * @property-read GiftCard $giftCard
 * @property-read User $user
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|GiftCardRecharge customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge last30Days(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge last7Days(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge lastYear(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge monthToDate(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge today(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge whereAmount($value)
 * @method static Builder<static>|GiftCardRecharge whereCreatedAt($value)
 * @method static Builder<static>|GiftCardRecharge whereGiftCardId($value)
 * @method static Builder<static>|GiftCardRecharge whereId($value)
 * @method static Builder<static>|GiftCardRecharge whereUpdatedAt($value)
 * @method static Builder<static>|GiftCardRecharge whereUserId($value)
 * @method static Builder<static>|GiftCardRecharge yearToDate(string $column = 'created_at')
 * @method static Builder<static>|GiftCardRecharge yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class GiftCardRecharge extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gift_card_recharges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gift_card_id',
        'amount',
        'user_id',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'gift_card_id' => 'integer',
        'amount' => 'float',
        'user_id' => 'integer',
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
                !empty($filters['gift_card_id']),
                fn(Builder $q) => $q->where('gift_card_id', $filters['gift_card_id'])
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
     * Get the gift card for this recharge.
     *
     * @return BelongsTo<GiftCard, self>
     */
    public function giftCard(): BelongsTo
    {
        return $this->belongsTo(GiftCard::class);
    }

    /**
     * Get the user who processed this recharge.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
