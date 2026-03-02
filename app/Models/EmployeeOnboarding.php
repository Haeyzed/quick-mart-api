<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class EmployeeOnboarding
 *
 * Represents an employee's onboarding process tied to a checklist template.
 * Handles the underlying data structure, relationships, and specific query scopes for employee onboarding entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $onboarding_checklist_template_id
 * @property string $status
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read OnboardingChecklistTemplate $template
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeOnboardingItem> $items
 * @property-read int|null $items_count
 *
 * @method static Builder|EmployeeOnboarding newModelQuery()
 * @method static Builder|EmployeeOnboarding newQuery()
 * @method static Builder|EmployeeOnboarding query()
 * @method static Builder|EmployeeOnboarding filter(array $filters)
 *
 * @method static Builder<static>|EmployeeOnboarding customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding whereCompletedAt($value)
 * @method static Builder<static>|EmployeeOnboarding whereCreatedAt($value)
 * @method static Builder<static>|EmployeeOnboarding whereEmployeeId($value)
 * @method static Builder<static>|EmployeeOnboarding whereId($value)
 * @method static Builder<static>|EmployeeOnboarding whereOnboardingChecklistTemplateId($value)
 * @method static Builder<static>|EmployeeOnboarding whereStartedAt($value)
 * @method static Builder<static>|EmployeeOnboarding whereStatus($value)
 * @method static Builder<static>|EmployeeOnboarding whereUpdatedAt($value)
 * @method static Builder<static>|EmployeeOnboarding yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboarding yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmployeeOnboarding extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'onboarding_checklist_template_id',
        'status',
        'started_at',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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
            ->when(
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', (int) $filters['employee_id'])
            )
            ->when(
                ! empty($filters['status']),
                fn (Builder $q) => $q->where('status', $filters['status'])
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the employee for this onboarding.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the checklist template for this onboarding.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistTemplate::class, 'onboarding_checklist_template_id');
    }

    /**
     * Get the checklist item progress entries.
     */
    public function items(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingItem::class, 'employee_onboarding_id');
    }
}
