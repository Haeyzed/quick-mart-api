<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class ExpenseCategory
 *
 * Represents a category for expenses. Handles the underlying data
 * structure, relationships, and specific query scopes for expense category entities.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|ExpenseCategory newModelQuery()
 * @method static Builder|ExpenseCategory newQuery()
 * @method static Builder|ExpenseCategory query()
 * @method static Builder|ExpenseCategory active()
 * @method static Builder|ExpenseCategory filter(array $filters)
 *
 * @property-read Collection<int, Expense> $expenses
 * @property-read int|null $expenses_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|ExpenseCategory customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory last30Days(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory last7Days(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory lastYear(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory monthToDate(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory onlyTrashed()
 * @method static Builder<static>|ExpenseCategory quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory today(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory whereCode($value)
 * @method static Builder<static>|ExpenseCategory whereCreatedAt($value)
 * @method static Builder<static>|ExpenseCategory whereDeletedAt($value)
 * @method static Builder<static>|ExpenseCategory whereId($value)
 * @method static Builder<static>|ExpenseCategory whereIsActive($value)
 * @method static Builder<static>|ExpenseCategory whereName($value)
 * @method static Builder<static>|ExpenseCategory whereUpdatedAt($value)
 * @method static Builder<static>|ExpenseCategory withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|ExpenseCategory withoutTrashed()
 * @method static Builder<static>|ExpenseCategory yearToDate(string $column = 'created_at')
 * @method static Builder<static>|ExpenseCategory yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class ExpenseCategory extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
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
                isset($filters['is_active']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('code', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active expense categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the expenses in this category.
     *
     * @return HasMany<Expense>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
