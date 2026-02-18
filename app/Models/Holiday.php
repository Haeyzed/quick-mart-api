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
 * Represents a holiday/leave request for a user.
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
 *
 * @method static Builder|Holiday approved()
 * @method static Builder|Holiday pending()
 * @method static Builder|Holiday filter(array $filters)
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
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include approved holidays.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include pending holidays.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope a query to apply filters.
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
     * Get the attributes that should be cast.
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
