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
 * Class IncomeCategory
 *
 * Represents a category for income. Handles the underlying data
 * structure, relationships, and specific query scopes for income category entities.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|IncomeCategory newModelQuery()
 * @method static Builder|IncomeCategory newQuery()
 * @method static Builder|IncomeCategory query()
 * @method static Builder|IncomeCategory active()
 * @method static Builder|IncomeCategory filter(array $filters)
 *
 * @property-read Collection<int, Income> $incomes
 * @property-read int|null $incomes_count
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|IncomeCategory customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory last30Days(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory last7Days(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory lastYear(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory monthToDate(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory onlyTrashed()
 * @method static Builder<static>|IncomeCategory quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory today(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory whereCode($value)
 * @method static Builder<static>|IncomeCategory whereCreatedAt($value)
 * @method static Builder<static>|IncomeCategory whereDeletedAt($value)
 * @method static Builder<static>|IncomeCategory whereId($value)
 * @method static Builder<static>|IncomeCategory whereIsActive($value)
 * @method static Builder<static>|IncomeCategory whereName($value)
 * @method static Builder<static>|IncomeCategory whereUpdatedAt($value)
 * @method static Builder<static>|IncomeCategory withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|IncomeCategory withoutTrashed()
 * @method static Builder<static>|IncomeCategory yearToDate(string $column = 'created_at')
 * @method static Builder<static>|IncomeCategory yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class IncomeCategory extends Model implements AuditableContract
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
     * Generate a unique 8-digit numeric code for income category.
     *
     * @return string Unique 8-digit code
     */
    public static function generateCode(): string
    {
        do {
            $code = str_pad((string) random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
        } while (self::where('code', $code)->where('is_active', true)->exists());

        return $code;
    }

    /**
     * Get the incomes in this category.
     *
     * @return HasMany<Income>
     */
    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    /**
     * Scope a query to only include active income categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
