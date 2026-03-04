<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class OnboardingChecklistItem
 * 
 * Represents a single checklist item within an onboarding checklist template.
 * Handles the underlying data structure, relationships, and specific query scopes for checklist item entities.
 *
 * @property int $id
 * @property int $onboarding_checklist_template_id
 * @property string $title
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read OnboardingChecklistTemplate $template
 * @property-read Collection<int, EmployeeOnboardingItem> $employeeOnboardingItems
 * @property-read int|null $employee_onboarding_items_count
 * @method static Builder|OnboardingChecklistItem newModelQuery()
 * @method static Builder|OnboardingChecklistItem newQuery()
 * @method static Builder|OnboardingChecklistItem query()
 * @method static Builder|OnboardingChecklistItem filter(array $filters)
 * @method static Builder<static>|OnboardingChecklistItem customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem last30Days(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem last7Days(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem lastYear(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem monthToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem today(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem whereCreatedAt($value)
 * @method static Builder<static>|OnboardingChecklistItem whereId($value)
 * @method static Builder<static>|OnboardingChecklistItem whereOnboardingChecklistTemplateId($value)
 * @method static Builder<static>|OnboardingChecklistItem whereSortOrder($value)
 * @method static Builder<static>|OnboardingChecklistItem whereTitle($value)
 * @method static Builder<static>|OnboardingChecklistItem whereUpdatedAt($value)
 * @method static Builder<static>|OnboardingChecklistItem yearToDate(string $column = 'created_at')
 * @method static Builder<static>|OnboardingChecklistItem yesterday(string $column = 'current_at')
 * @mixin Eloquent
 */
class OnboardingChecklistItem extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'onboarding_checklist_template_id',
        'title',
        'sort_order',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'sort_order' => 'integer',
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
            ->when(
                !empty($filters['onboarding_checklist_template_id']),
                fn(Builder $q) => $q->where('onboarding_checklist_template_id', (int)$filters['onboarding_checklist_template_id'])
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('title', 'like', $term);
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the template this checklist item belongs to.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistTemplate::class, 'onboarding_checklist_template_id');
    }

    /**
     * Get the employee onboarding items created from this checklist item.
     */
    public function employeeOnboardingItems(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingItem::class, 'onboarding_checklist_item_id');
    }
}
