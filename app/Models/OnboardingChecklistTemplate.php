<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class OnboardingChecklistTemplate
 *
 * Represents an onboarding checklist template within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for template entities.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|OnboardingChecklistTemplate newModelQuery()
 * @method static Builder|OnboardingChecklistTemplate newQuery()
 * @method static Builder|OnboardingChecklistTemplate query()
 * @method static Builder|OnboardingChecklistTemplate filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeOnboarding> $employeeOnboardings
 * @property-read int|null $employee_onboardings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, OnboardingChecklistItem> $items
 * @property-read int|null $items_count
 *
 * @method static Builder<static>|OnboardingChecklistTemplate customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate last30Days(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate last7Days(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate lastYear(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate monthToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate today(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate whereCreatedAt($value)
 * @method static Builder<static>|OnboardingChecklistTemplate whereId($value)
 * @method static Builder<static>|OnboardingChecklistTemplate whereIsDefault($value)
 * @method static Builder<static>|OnboardingChecklistTemplate whereName($value)
 * @method static Builder<static>|OnboardingChecklistTemplate whereUpdatedAt($value)
 * @method static Builder<static>|OnboardingChecklistTemplate yearToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistTemplate yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class OnboardingChecklistTemplate extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'is_default',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'is_default' => 'boolean',
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
                isset($filters['is_default']),
                fn (Builder $q) => $q->where('is_default', filter_var($filters['is_default'], FILTER_VALIDATE_BOOLEAN))
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the checklist items for this template.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OnboardingChecklistItem::class, 'onboarding_checklist_template_id')->orderBy('sort_order');
    }

    /**
     * Get the employee onboardings using this template.
     */
    public function employeeOnboardings(): HasMany
    {
        return $this->hasMany(EmployeeOnboarding::class, 'onboarding_checklist_template_id');
    }
}
