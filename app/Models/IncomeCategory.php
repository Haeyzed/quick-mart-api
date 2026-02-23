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
 * IncomeCategory Model
 * 
 * Represents a category for income.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Income> $incomes
 * @method static Builder|IncomeCategory active()
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $incomes_count
 * @method static Builder<static>|IncomeCategory newModelQuery()
 * @method static Builder<static>|IncomeCategory newQuery()
 * @method static Builder<static>|IncomeCategory onlyTrashed()
 * @method static Builder<static>|IncomeCategory query()
 * @method static Builder<static>|IncomeCategory whereCode($value)
 * @method static Builder<static>|IncomeCategory whereCreatedAt($value)
 * @method static Builder<static>|IncomeCategory whereDeletedAt($value)
 * @method static Builder<static>|IncomeCategory whereId($value)
 * @method static Builder<static>|IncomeCategory whereIsActive($value)
 * @method static Builder<static>|IncomeCategory whereName($value)
 * @method static Builder<static>|IncomeCategory whereUpdatedAt($value)
 * @method static Builder<static>|IncomeCategory withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|IncomeCategory withoutTrashed()
 * @mixin \Eloquent
 */
class IncomeCategory extends Model implements AuditableContract
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
     * Generate a unique 8-digit numeric code for income category.
     *
     * @return string Unique 8-digit code
     */
    public static function generateCode(): string
    {
        do {
            $code = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
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
