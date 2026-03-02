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
 * Class ActivityLog
 *
 * Represents an activity log entry for user actions. Handles the underlying data
 * structure, relationships, and specific query scopes for activity log entities.
 *
 * @property int $id
 * @property Carbon $date
 * @property int $user_id
 * @property string $action
 * @property string|null $reference_no
 * @property string|null $item_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|ActivityLog newModelQuery()
 * @method static Builder|ActivityLog newQuery()
 * @method static Builder|ActivityLog query()
 * @method static Builder|ActivityLog filter(array $filters)
 *
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|ActivityLog customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|ActivityLog last30Days(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog last7Days(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog lastYear(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog monthToDate(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog today(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog whereAction($value)
 * @method static Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static Builder<static>|ActivityLog whereDate($value)
 * @method static Builder<static>|ActivityLog whereId($value)
 * @method static Builder<static>|ActivityLog whereItemDescription($value)
 * @method static Builder<static>|ActivityLog whereReferenceNo($value)
 * @method static Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static Builder<static>|ActivityLog whereUserId($value)
 * @method static Builder<static>|ActivityLog yearToDate(string $column = 'created_at')
 * @method static Builder<static>|ActivityLog yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class ActivityLog extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'user_id',
        'action',
        'reference_no',
        'item_description',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
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
                ! empty($filters['user_id']),
                fn (Builder $q) => $q->where('user_id', $filters['user_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('action', 'like', $term)
                        ->orWhere('reference_no', 'like', $term)
                        ->orWhere('item_description', 'like', $term)
                        ->orWhereHas('user', function (Builder $userQ) use ($term) {
                            $userQ->where('name', 'like', $term);
                        })
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
                'date'
            );
    }

    /**
     * Get the user who performed this action.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
