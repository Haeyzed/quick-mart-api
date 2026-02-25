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
 * Represents a holiday request or record within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for holiday entities.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $from_date
 * @property Carbon $to_date
 * @property string|null $note
 * @property bool $is_approved
 * @property bool $recurring
 * @property string|null $region
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Holiday newModelQuery()
 * @method static Builder|Holiday newQuery()
 * @method static Builder|Holiday query()
 * @method static Builder|Holiday approved()
 * @method static Builder|Holiday filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \App\Models\User $user
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
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'holidays';

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
        'region'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'from_date' => 'date:Y-m-d',
        'to_date' => 'date:Y-m-d',
        'is_approved' => 'boolean',
        'recurring' => 'boolean',
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
                isset($filters['is_approved']),
                fn (Builder $q) => $q->where('is_approved', filter_var($filters['is_approved'], FILTER_VALIDATE_BOOLEAN))
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
                        ->orWhereHas('user', function (Builder $subQ) use ($term) {
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
     * Get the user that created or requested the holiday.
     *
     * Defines a one-to-many relationship linking this holiday to its requesting user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
