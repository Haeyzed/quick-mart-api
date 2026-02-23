<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Holiday
 * 
 * Represents a holiday/leave request within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for holiday entities.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $from_date
 * @property Carbon $to_date
 * @property string|null $note
 * @property bool $is_approved
 * @property bool|null $recurring
 * @property string|null $region
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @method static Builder|Holiday newModelQuery()
 * @method static Builder|Holiday newQuery()
 * @method static Builder|Holiday query()
 * @method static Builder|Holiday approved()
 * @method static Builder|Holiday pending()
 * @method static Builder|Holiday filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Holiday customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Holiday last30Days(string $column = 'created_at')
 * @method static Builder<static>|Holiday last7Days(string $column = 'created_at')
 * @method static Builder<static>|Holiday lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Holiday lastYear(string $column = 'created_at')
 * @method static Builder<static>|Holiday monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Holiday onlyTrashed()
 * @method static Builder<static>|Holiday quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Holiday today(string $column = 'created_at')
 * @method static Builder<static>|Holiday whereCreatedAt($value)
 * @method static Builder<static>|Holiday whereDeletedAt($value)
 * @method static Builder<static>|Holiday whereFromDate($value)
 * @method static Builder<static>|Holiday whereId($value)
 * @method static Builder<static>|Holiday whereIsApproved($value)
 * @method static Builder<static>|Holiday whereNote($value)
 * @method static Builder<static>|Holiday whereRecurring($value)
 * @method static Builder<static>|Holiday whereRegion($value)
 * @method static Builder<static>|Holiday whereToDate($value)
 * @method static Builder<static>|Holiday whereUpdatedAt($value)
 * @method static Builder<static>|Holiday whereUserId($value)
 * @method static Builder<static>|Holiday withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Holiday withoutTrashed()
 * @method static Builder<static>|Holiday yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Holiday yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Holiday extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'from_date',
        'to_date',
        'note',
        'is_approved',
        'recurring',
        'region',
    ];

    /**
     * Get the user for this holiday.
     *
     * Defines a many-to-one relationship linking this holiday to its user.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include approved holidays.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include pending holidays.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope a query to apply dynamic filters.
     *
     * Applies filters for user_id, approval status, search (note), and date range on from_date.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['user_id']),
                fn (Builder $q) => $q->where('user_id', (int) $filters['user_id'])
            )
            ->when(
                isset($filters['is_approved']),
                fn (Builder $q) => $q->where('is_approved', (bool) $filters['is_approved'])
            )
            ->when(
                ! empty($filters['search'] ?? null),
                fn (Builder $q) => $q->where('note', 'like', '%'.$filters['search'].'%')
            )
            ->customRange(
                $filters['start_date'] ?? null,
                $filters['end_date'] ?? null,
                'from_date'
            );
    }

    /**
     * Get the attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'from_date' => 'date',
            'to_date' => 'date',
            'is_approved' => 'boolean',
            'recurring' => 'boolean',
        ];
    }
}
