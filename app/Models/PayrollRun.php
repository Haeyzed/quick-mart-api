<?php

declare(strict_types=1);

namespace App\Models;

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
 * Class PayrollRun
 * 
 * Represents a payroll run for a given month/year within the system. Handles the
 * underlying data structure, relationships, and batch of payroll entries for a period.
 *
 * @property int $id
 * @property string $month
 * @property int $year
 * @property string $status
 * @property int|null $generated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|PayrollRun newModelQuery()
 * @method static Builder|PayrollRun newQuery()
 * @method static Builder|PayrollRun query()
 * @method static Builder|PayrollRun filter(array $filters)
 * @property-read Collection<int, PayrollEntry> $entries
 * @property-read int|null $entries_count
 * @property-read User|null $generatedByUser
 * @method static Builder<static>|PayrollRun onlyTrashed()
 * @method static Builder<static>|PayrollRun whereCreatedAt($value)
 * @method static Builder<static>|PayrollRun whereDeletedAt($value)
 * @method static Builder<static>|PayrollRun whereGeneratedBy($value)
 * @method static Builder<static>|PayrollRun whereId($value)
 * @method static Builder<static>|PayrollRun whereMonth($value)
 * @method static Builder<static>|PayrollRun whereStatus($value)
 * @method static Builder<static>|PayrollRun whereUpdatedAt($value)
 * @method static Builder<static>|PayrollRun whereYear($value)
 * @method static Builder<static>|PayrollRun withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|PayrollRun withoutTrashed()
 * @mixin Eloquent
 */
class PayrollRun extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'month',
        'year',
        'status',
        'generated_by',
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
            ->when(!empty($filters['year']), fn(Builder $q) => $q->where('year', (int)$filters['year']))
            ->when(!empty($filters['month']), fn(Builder $q) => $q->where('month', $filters['month']));
    }

    /**
     * Get the user who generated this payroll run.
     */
    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Get the payroll entries belonging to this run.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class, 'payroll_run_id');
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }
}
