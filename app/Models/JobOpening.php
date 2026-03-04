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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class JobOpening
 * 
 * Represents a job opening within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for job opening entities.
 *
 * @property int $id
 * @property string $title
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property string $status
 * @property string|null $description
 * @property int|null $openings_count
 * @property int|null $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|JobOpening newModelQuery()
 * @method static Builder|JobOpening newQuery()
 * @method static Builder|JobOpening query()
 * @method static Builder|JobOpening filter(array $filters)
 * @property-read User|null $createdByUser
 * @property-read Collection<int, Candidate> $candidates
 * @property-read int|null $candidates_count
 * @property-read Department|null $department
 * @property-read Designation|null $designation
 * @method static Builder<static>|JobOpening customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|JobOpening last30Days(string $column = 'created_at')
 * @method static Builder<static>|JobOpening last7Days(string $column = 'created_at')
 * @method static Builder<static>|JobOpening lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|JobOpening lastYear(string $column = 'created_at')
 * @method static Builder<static>|JobOpening monthToDate(string $column = 'created_at')
 * @method static Builder<static>|JobOpening onlyTrashed()
 * @method static Builder<static>|JobOpening quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|JobOpening today(string $column = 'created_at')
 * @method static Builder<static>|JobOpening whereCreatedAt($value)
 * @method static Builder<static>|JobOpening whereCreatedBy($value)
 * @method static Builder<static>|JobOpening whereDeletedAt($value)
 * @method static Builder<static>|JobOpening whereDepartmentId($value)
 * @method static Builder<static>|JobOpening whereDesignationId($value)
 * @method static Builder<static>|JobOpening whereDescription($value)
 * @method static Builder<static>|JobOpening whereId($value)
 * @method static Builder<static>|JobOpening whereOpeningsCount($value)
 * @method static Builder<static>|JobOpening whereStatus($value)
 * @method static Builder<static>|JobOpening whereTitle($value)
 * @method static Builder<static>|JobOpening whereUpdatedAt($value)
 * @method static Builder<static>|JobOpening withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|JobOpening withoutTrashed()
 * @method static Builder<static>|JobOpening yearToDate(string $column = 'created_at')
 * @method static Builder<static>|JobOpening yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class JobOpening extends Model
{
    use FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'department_id',
        'designation_id',
        'status',
        'description',
        'openings_count',
        'created_by',
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
            ->when(!empty($filters['status']), fn(Builder $q) => $q->where('status', $filters['status']))
            ->when(
                !empty($filters['search']),
                fn(Builder $q) => $q->where('title', 'like', '%' . $filters['search'] . '%')
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the department associated with this job opening.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation associated with this job opening.
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get the user who created this job opening.
     */
    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the candidates applied for this job opening.
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class, 'job_opening_id');
    }
}
