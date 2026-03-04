<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class Interview
 * 
 * Represents an interview within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for interview entities.
 *
 * @property int $id
 * @property int $candidate_id
 * @property Carbon $scheduled_at
 * @property int|null $duration_minutes
 * @property int|null $interviewer_id
 * @property string|null $feedback
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Interview newModelQuery()
 * @method static Builder|Interview newQuery()
 * @method static Builder|Interview query()
 * @method static Builder|Interview filter(array $filters)
 * @property-read Candidate $candidate
 * @property-read User|null $interviewer
 * @method static Builder<static>|Interview customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Interview last30Days(string $column = 'created_at')
 * @method static Builder<static>|Interview last7Days(string $column = 'created_at')
 * @method static Builder<static>|Interview lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Interview lastYear(string $column = 'created_at')
 * @method static Builder<static>|Interview monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Interview quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Interview today(string $column = 'created_at')
 * @method static Builder<static>|Interview whereCandidateId($value)
 * @method static Builder<static>|Interview whereCreatedAt($value)
 * @method static Builder<static>|Interview whereDurationMinutes($value)
 * @method static Builder<static>|Interview whereFeedback($value)
 * @method static Builder<static>|Interview whereId($value)
 * @method static Builder<static>|Interview whereInterviewerId($value)
 * @method static Builder<static>|Interview whereScheduledAt($value)
 * @method static Builder<static>|Interview whereStatus($value)
 * @method static Builder<static>|Interview whereUpdatedAt($value)
 * @method static Builder<static>|Interview yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Interview yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class Interview extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'candidate_id',
        'scheduled_at',
        'duration_minutes',
        'interviewer_id',
        'feedback',
        'status',
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
            ->when(!empty($filters['candidate_id']), fn(Builder $q) => $q->where('candidate_id', (int)$filters['candidate_id']))
            ->when(!empty($filters['status']), fn(Builder $q) => $q->where('status', $filters['status']))
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the candidate associated with this interview.
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    /**
     * Get the user (interviewer) associated with this interview.
     */
    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }
}
