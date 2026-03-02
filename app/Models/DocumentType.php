<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class DocumentType
 *
 * Represents a type of document that can be attached to employees (e.g. ID, contract).
 * Handles the underlying data structure, relationships, and specific query scopes for document type entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property bool $requires_expiry
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|DocumentType newModelQuery()
 * @method static Builder|DocumentType newQuery()
 * @method static Builder|DocumentType query()
 * @method static Builder|DocumentType active()
 * @method static Builder|DocumentType filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EmployeeDocument> $employeeDocuments
 * @property-read int|null $employee_documents_count
 *
 * @method static Builder<static>|DocumentType customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|DocumentType last30Days(string $column = 'created_at')
 * @method static Builder<static>|DocumentType last7Days(string $column = 'created_at')
 * @method static Builder<static>|DocumentType lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|DocumentType lastYear(string $column = 'created_at')
 * @method static Builder<static>|DocumentType monthToDate(string $column = 'created_at')
 * @method static Builder<static>|DocumentType onlyTrashed()
 * @method static Builder<static>|DocumentType quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|DocumentType today(string $column = 'created_at')
 * @method static Builder<static>|DocumentType whereCode($value)
 * @method static Builder<static>|DocumentType whereCreatedAt($value)
 * @method static Builder<static>|DocumentType whereDeletedAt($value)
 * @method static Builder<static>|DocumentType whereId($value)
 * @method static Builder<static>|DocumentType whereIsActive($value)
 * @method static Builder<static>|DocumentType whereName($value)
 * @method static Builder<static>|DocumentType whereRequiresExpiry($value)
 * @method static Builder<static>|DocumentType whereUpdatedAt($value)
 * @method static Builder<static>|DocumentType withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|DocumentType withoutTrashed()
 * @method static Builder<static>|DocumentType yearToDate(string $column = 'created_at')
 * @method static Builder<static>|DocumentType yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class DocumentType extends Model
{
    use FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'requires_expiry',
        'is_active',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'requires_expiry' => 'boolean',
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
                    $term = '%'.$filters['search'].'%';
                    $q->where('name', 'like', $term)->orWhere('code', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active document types.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the employee documents of this type.
     */
    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class, 'document_type_id');
    }
}
