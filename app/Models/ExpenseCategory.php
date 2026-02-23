<?php

declare(strict_types=1);

namespace App\Models;

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
 * ExpenseCategory Model
 * 
 * Represents a category for expenses.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Expense> $expenses
 * @method static Builder|ExpenseCategory active()
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $expenses_count
 * @method static Builder<static>|ExpenseCategory newModelQuery()
 * @method static Builder<static>|ExpenseCategory newQuery()
 * @method static Builder<static>|ExpenseCategory onlyTrashed()
 * @method static Builder<static>|ExpenseCategory query()
 * @method static Builder<static>|ExpenseCategory whereCode($value)
 * @method static Builder<static>|ExpenseCategory whereCreatedAt($value)
 * @method static Builder<static>|ExpenseCategory whereDeletedAt($value)
 * @method static Builder<static>|ExpenseCategory whereId($value)
 * @method static Builder<static>|ExpenseCategory whereIsActive($value)
 * @method static Builder<static>|ExpenseCategory whereName($value)
 * @method static Builder<static>|ExpenseCategory whereUpdatedAt($value)
 * @method static Builder<static>|ExpenseCategory withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|ExpenseCategory withoutTrashed()
 * @mixin \Eloquent
 */
class ExpenseCategory extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes;

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
     * Get the expenses in this category.
     *
     * @return HasMany<Expense>
     */
    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Scope a query to only include active expense categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
