<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class EmployeeOnboardingItem
 *
 * Represents a single checklist item progress entry within an employee onboarding.
 * Handles the underlying data structure, relationships, and specific query scopes for employee onboarding item entities.
 *
 * @property int $id
 * @property int $employee_onboarding_id
 * @property int $onboarding_checklist_item_id
 * @property Carbon|null $completed_at
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read EmployeeOnboarding $employeeOnboarding
 * @property-read OnboardingChecklistItem $checklistItem
 *
 * @method static Builder|EmployeeOnboardingItem newModelQuery()
 * @method static Builder|EmployeeOnboardingItem newQuery()
 * @method static Builder|EmployeeOnboardingItem query()
 * @method static Builder|EmployeeOnboardingItem filter(array $filters)
 *
 * @method static Builder<static>|EmployeeOnboardingItem customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem whereCompletedAt($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereCreatedAt($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereEmployeeOnboardingId($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereId($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereNotes($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereOnboardingChecklistItemId($value)
 * @method static Builder<static>|EmployeeOnboardingItem whereUpdatedAt($value)
 * @method static Builder<static>|EmployeeOnboardingItem yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeOnboardingItem yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmployeeOnboardingItem extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_onboarding_id',
        'onboarding_checklist_item_id',
        'completed_at',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
                ! empty($filters['employee_onboarding_id']),
                fn (Builder $q) => $q->where('employee_onboarding_id', (int) $filters['employee_onboarding_id'])
            )
            ->when(
                isset($filters['completed']),
                fn (Builder $q) => $filters['completed']
                    ? $q->whereNotNull('completed_at')
                    : $q->whereNull('completed_at')
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the parent employee onboarding.
     */
    public function employeeOnboarding(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboarding::class);
    }

    /**
     * Get the checklist item template.
     */
    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistItem::class, 'onboarding_checklist_item_id');
    }
}
