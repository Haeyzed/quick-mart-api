<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class Candidate
 *
 * Represents a job candidate within the recruitment workflow. Handles the underlying data
 * structure, relationships, and specific query scopes for candidate entities.
 *
 * @property int $id
 * @property int $job_opening_id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $source
 * @property string|null $stage
 * @property Carbon|null $stage_updated_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Candidate newModelQuery()
 * @method static Builder|Candidate newQuery()
 * @method static Builder|Candidate query()
 * @method static Builder|Candidate filter(array $filters)
 *
 * @property-read \App\Models\JobOpening $jobOpening
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Interview> $interviews
 * @property-read int|null $interviews_count
 *
 * @method static Builder<static>|Candidate customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Candidate last30Days(string $column = 'created_at')
 * @method static Builder<static>|Candidate last7Days(string $column = 'created_at')
 * @method static Builder<static>|Candidate lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Candidate lastYear(string $column = 'created_at')
 * @method static Builder<static>|Candidate monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Candidate onlyTrashed()
 * @method static Builder<static>|Candidate quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Candidate today(string $column = 'created_at')
 * @method static Builder<static>|Candidate whereCreatedAt($value)
 * @method static Builder<static>|Candidate whereDeletedAt($value)
 * @method static Builder<static>|Candidate whereEmail($value)
 * @method static Builder<static>|Candidate whereId($value)
 * @method static Builder<static>|Candidate whereJobOpeningId($value)
 * @method static Builder<static>|Candidate whereName($value)
 * @method static Builder<static>|Candidate whereNotes($value)
 * @method static Builder<static>|Candidate wherePhone($value)
 * @method static Builder<static>|Candidate whereSource($value)
 * @method static Builder<static>|Candidate whereStage($value)
 * @method static Builder<static>|Candidate whereStageUpdatedAt($value)
 * @method static Builder<static>|Candidate whereUpdatedAt($value)
 * @method static Builder<static>|Candidate withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Candidate withoutTrashed()
 * @method static Builder<static>|Candidate yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Candidate yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Candidate extends Model
{
    use FilterableByDates, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'job_opening_id',
        'name',
        'email',
        'phone',
        'source',
        'stage',
        'stage_updated_at',
        'notes',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'stage_updated_at' => 'datetime',
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
                ! empty($filters['job_opening_id']),
                fn (Builder $q) => $q->where('job_opening_id', $filters['job_opening_id'])
            )
            ->when(
                ! empty($filters['stage']),
                fn (Builder $q) => $q->where('stage', $filters['stage'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the job opening this candidate applied for.
     */
    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    /**
     * Get the interviews for this candidate.
     */
    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}
