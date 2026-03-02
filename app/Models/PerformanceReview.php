<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class PerformanceReview
 *
 * Represents a performance review within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for performance review entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $review_period_start
 * @property Carbon $review_period_end
 * @property int|null $reviewer_id
 * @property float|null $overall_rating
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $promotion_effective_date
 * @property int|null $new_designation_id
 * @property Carbon|null $submitted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|PerformanceReview newModelQuery()
 * @method static Builder|PerformanceReview newQuery()
 * @method static Builder|PerformanceReview query()
 * @method static Builder|PerformanceReview filter(array $filters)
 *
 * @property-read \App\Models\Employee $employee
 * @property-read \App\Models\Designation|null $newDesignation
 * @property-read \App\Models\User|null $reviewer
 *
 * @mixin \Eloquent
 */
class PerformanceReview extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'review_period_start',
        'review_period_end',
        'reviewer_id',
        'overall_rating',
        'status',
        'notes',
        'promotion_effective_date',
        'new_designation_id',
        'submitted_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'review_period_start' => 'date',
            'review_period_end' => 'date',
            'promotion_effective_date' => 'date',
            'submitted_at' => 'datetime',
            'overall_rating' => 'decimal:2',
        ];
    }

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
            ->when(! empty($filters['employee_id']), fn (Builder $q) => $q->where('employee_id', (int) $filters['employee_id']))
            ->when(! empty($filters['status']), fn (Builder $q) => $q->where('status', $filters['status']));
    }

    /**
     * Get the employee associated with this performance review.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user (reviewer) associated with this performance review.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the new designation (for promotion) associated with this review.
     */
    public function newDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'new_designation_id');
    }
}
